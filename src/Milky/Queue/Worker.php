<?php namespace Milky\Queue;

use Exception;
use Milky\Cache\Repository as Cache;
use Milky\Exceptions\Handler;
use Milky\Framework;
use Milky\Queue\Failed\FailedJobProviderInterface;
use Milky\Queue\Jobs\Job;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Throwable;

class Worker
{
	/**
	 * The queue manager instance.
	 *
	 * @var QueueManager
	 */
	protected $manager;

	/**
	 * The failed job provider implementation.
	 *
	 * @var FailedJobProviderInterface
	 */
	protected $failer;

	/**
	 * The cache Cache implementation.
	 *
	 * @var Cache
	 */
	protected $cache;

	/**
	 * The exception handler instance.
	 *
	 * @var Handler
	 */
	protected $exceptions;

	/**
	 * Create a new queue worker.
	 *
	 * @param  QueueManager $manager
	 * @param  FailedJobProviderInterface $failer
	 */
	public function __construct( QueueManager $manager, FailedJobProviderInterface $failer = null )
	{
		$this->failer = $failer;
		$this->manager = $manager;
	}

	/**
	 * Listen to the given queue in a loop.
	 *
	 * @param  string $connectionName
	 * @param  string $queue
	 * @param  int $delay
	 * @param  int $memory
	 * @param  int $sleep
	 * @param  int $maxTries
	 * @return array
	 */
	public function daemon( $connectionName, $queue = null, $delay = 0, $memory = 128, $sleep = 3, $maxTries = 0 )
	{
		$lastRestart = $this->getTimestampOfLastQueueRestart();

		while ( true )
		{
			if ( $this->daemonShouldRun() )
			{
				$this->runNextJobForDaemon( $connectionName, $queue, $delay, $sleep, $maxTries );
			}
			else
			{
				$this->sleep( $sleep );
			}

			if ( $this->memoryExceeded( $memory ) || $this->queueShouldRestart( $lastRestart ) )
			{
				$this->stop();
			}
		}
	}

	/**
	 * Run the next job for the daemon worker.
	 *
	 * @param  string $connectionName
	 * @param  string $queue
	 * @param  int $delay
	 * @param  int $sleep
	 * @param  int $maxTries
	 */
	protected function runNextJobForDaemon( $connectionName, $queue, $delay, $sleep, $maxTries )
	{
		try
		{
			$this->pop( $connectionName, $queue, $delay, $sleep, $maxTries );
		}
		catch ( Exception $e )
		{
			if ( $this->exceptions )
				$this->exceptions->report( $e );
		}
		catch ( Throwable $e )
		{
			if ( $this->exceptions )
				$this->exceptions->report( new FatalThrowableError( $e ) );
		}
	}

	/**
	 * Determine if the daemon should process on this iteration.
	 *
	 * @return bool
	 */
	protected function daemonShouldRun()
	{
		return $this->manager->isDownForMaintenance() ? false : Framework::hooks()->until( 'queue.looping' ) !== false;
	}

	/**
	 * Listen to the given queue.
	 *
	 * @param  string $connectionName
	 * @param  string $queue
	 * @param  int $delay
	 * @param  int $sleep
	 * @param  int $maxTries
	 * @return array
	 */
	public function pop( $connectionName, $queue = null, $delay = 0, $sleep = 3, $maxTries = 0 )
	{
		try
		{
			$connection = $this->manager->connection( $connectionName );

			$job = $this->getNextJob( $connection, $queue );

			// If we're able to pull a job off of the stack, we will process it and
			// then immediately return back out. If there is no job on the queue
			// we will "sleep" the worker for the specified number of seconds.
			if ( !is_null( $job ) )
				return $this->process( $this->manager->getName( $connectionName ), $job, $maxTries, $delay );
		}
		catch ( Exception $e )
		{
			if ( $this->exceptions )
				$this->exceptions->report( $e );
		}

		$this->sleep( $sleep );

		return ['job' => null, 'failed' => false];
	}

	/**
	 * Get the next job from the queue connection.
	 *
	 * @param  Queue $connection
	 * @param  string $queue
	 * @return Job|null
	 */
	protected function getNextJob( $connection, $queue )
	{
		if ( is_null( $queue ) )
			return $connection->pop();

		foreach ( explode( ',', $queue ) as $queue )
			if ( !is_null( $job = $connection->pop( $queue ) ) )
				return $job;

		return null;
	}

	/**
	 * Process a given job from the queue.
	 *
	 * @param  string $connection
	 * @param  Job $job
	 * @param  int $maxTries
	 * @param  int $delay
	 * @return array|null
	 *
	 * @throws \Throwable
	 */
	public function process( $connection, Job $job, $maxTries = 0, $delay = 0 )
	{
		if ( $maxTries > 0 && $job->attempts() > $maxTries )
			return $this->logFailedJob( $connection, $job );

		try
		{
			$this->raiseBeforeJobEvent( $connection, $job );

			// First we will fire off the job. Once it is done we will see if it will be
			// automatically deleted after processing and if so we'll fire the delete
			// method on the job. Otherwise, we will just keep on running our jobs.
			$job->fire();

			$this->raiseAfterJobEvent( $connection, $job );

			return ['job' => $job, 'failed' => false];
		}
		catch ( Exception $e )
		{
			$this->handleJobException( $connection, $job, $delay, $e );
		}
		catch ( Throwable $e )
		{
			$this->handleJobException( $connection, $job, $delay, $e );
		}

		return null;
	}

	/**
	 * Handle an exception that occurred while the job was running.
	 *
	 * @param  string $connection
	 * @param  Job $job
	 * @param  int $delay
	 * @param  \Throwable $e
	 * @return void
	 *
	 * @throws \Throwable
	 */
	protected function handleJobException( $connection, Job $job, $delay, $e )
	{
		// If we catch an exception, we will attempt to release the job back onto
		// the queue so it is not lost. This will let is be retried at a later
		// time by another listener (or the same one). We will do that here.
		try
		{
			$this->raiseExceptionOccurredJobEvent( $connection, $job, $e );
		}
		finally
		{
			if ( !$job->isDeleted() )
			{
				$job->release( $delay );
			}
		}

		throw $e;
	}

	/**
	 * Raise the before queue job event.
	 *
	 * @param  string $connection
	 * @param  Job $job
	 * @return void
	 */
	protected function raiseBeforeJobEvent( $connection, Job $job )
	{
		$data = json_decode( $job->getRawBody(), true );
		Framework::hooks()->trigger( 'queue.job.processing', compact( 'connection', 'job', 'data' ) );
	}

	/**
	 * Raise the after queue job event.
	 *
	 * @param  string $connection
	 * @param  Job $job
	 * @return void
	 */
	protected function raiseAfterJobEvent( $connection, Job $job )
	{
		$data = json_decode( $job->getRawBody(), true );
		Framework::hooks()->trigger( 'queue.job.processed', compact( 'connection', 'job', 'data' ) );
	}

	/**
	 * Raise the exception occurred queue job event.
	 *
	 * @param  string $connection
	 * @param  Job $job
	 * @param  \Throwable $exception
	 * @return void
	 */
	protected function raiseExceptionOccurredJobEvent( $connection, Job $job, $exception )
	{
		$data = json_decode( $job->getRawBody(), true );
		Framework::hooks()->trigger( 'queue.job.exception', compact( 'connection', 'job', 'data', 'exception' ) );
	}

	/**
	 * Log a failed job into storage.
	 *
	 * @param  string $connection
	 * @param  Job $job
	 * @return array
	 */
	protected function logFailedJob( $connection, Job $job )
	{
		if ( $this->failer )
		{
			$failedId = $this->failer->log( $connection, $job->getQueue(), $job->getRawBody() );
			$job->delete();
			$job->failed();
			$this->raiseFailedJobEvent( $connection, $job, $failedId );
		}

		return ['job' => $job, 'failed' => true];
	}

	/**
	 * Raise the failed queue job event.
	 *
	 * @param  string $connection
	 * @param  Job $job
	 * @param  int|null $failedId
	 * @return void
	 */
	protected function raiseFailedJobEvent( $connection, Job $job, $failedId )
	{
		$data = json_decode( $job->getRawBody(), true );
		Framework::hooks()->trigger( 'queue.job.failed', compact( 'connection', 'job', 'data', 'failedId' ) );
	}

	/**
	 * Determine if the memory limit has been exceeded.
	 *
	 * @param  int $memoryLimit
	 * @return bool
	 */
	public function memoryExceeded( $memoryLimit )
	{
		return ( memory_get_usage() / 1024 / 1024 ) >= $memoryLimit;
	}

	/**
	 * Stop listening and bail out of the script.
	 *
	 * @return void
	 */
	public function stop()
	{
		Framework::hooks()->trigger( 'queue.worker.stopping' );
		die;
	}

	/**
	 * Sleep the script for a given number of seconds.
	 *
	 * @param  int $seconds
	 * @return void
	 */
	public function sleep( $seconds )
	{
		sleep( $seconds );
	}

	/**
	 * Get the last queue restart timestamp, or null.
	 *
	 * @return int|null
	 */
	protected function getTimestampOfLastQueueRestart()
	{
		if ( $this->cache )
			return $this->cache->get( 'illuminate:queue:restart' );

		return null;
	}

	/**
	 * Determine if the queue worker should restart.
	 *
	 * @param  int|null $lastRestart
	 * @return bool
	 */
	protected function queueShouldRestart( $lastRestart )
	{
		return $this->getTimestampOfLastQueueRestart() != $lastRestart;
	}

	/**
	 * Set the exception handler to use in Daemon mode.
	 *
	 * @param  Handler $handler
	 * @return void
	 */
	public function setDaemonExceptionHandler( Handler $handler )
	{
		$this->exceptions = $handler;
	}

	/**
	 * Set the cache Cache implementation.
	 *
	 * @param  Cache $cache
	 * @return void
	 */
	public function setCache( Cache $cache )
	{
		$this->cache = $cache;
	}

	/**
	 * Get the queue manager instance.
	 *
	 * @return QueueManager
	 */
	public function getManager()
	{
		return $this->manager;
	}

	/**
	 * Set the queue manager instance.
	 *
	 * @param  QueueManager $manager
	 * @return void
	 */
	public function setManager( QueueManager $manager )
	{
		$this->manager = $manager;
	}
}
