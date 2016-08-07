<?php namespace Milky\Queue;

use Exception;
use Milky\Facades\Hooks;
use Milky\Queue\Jobs\Job;
use Milky\Queue\Jobs\SyncJob;
use Throwable;

class SyncQueue extends Queue
{
	/**
	 * Push a new job onto the queue.
	 *
	 * @param  string $job
	 * @param  mixed $data
	 * @param  string $queue
	 * @return mixed
	 *
	 * @throws Throwable
	 */
	public function push( $job, $data = '', $queue = null )
	{
		$queueJob = $this->resolveJob( $this->createPayload( $job, $data, $queue ) );

		try
		{
			$this->raiseBeforeJobEvent( $queueJob );

			$queueJob->fire();

			$this->raiseAfterJobEvent( $queueJob );
		}
		catch ( Exception $e )
		{
			$this->raiseExceptionOccurredJobEvent( $queueJob, $e );

			$this->handleFailedJob( $queueJob );

			throw $e;
		}
		catch ( Throwable $e )
		{
			$this->raiseExceptionOccurredJobEvent( $queueJob, $e );

			$this->handleFailedJob( $queueJob );

			throw $e;
		}

		return 0;
	}

	/**
	 * Push a raw payload onto the queue.
	 *
	 * @param  string $payload
	 * @param  string $queue
	 * @param  array $options
	 * @return mixed
	 */
	public function pushRaw( $payload, $queue = null, array $options = [] )
	{
		//
	}

	/**
	 * Push a new job onto the queue after a delay.
	 *
	 * @param  \DateTime|int $delay
	 * @param  string $job
	 * @param  mixed $data
	 * @param  string $queue
	 * @return mixed
	 */
	public function later( $delay, $job, $data = '', $queue = null )
	{
		return $this->push( $job, $data, $queue );
	}

	/**
	 * Pop the next job off of the queue.
	 *
	 * @param  string $queue
	 * @return Job|null
	 */
	public function pop( $queue = null )
	{
		//
	}

	/**
	 * Resolve a Sync job instance.
	 *
	 * @param  string $payload
	 * @return SyncJob
	 */
	protected function resolveJob( $payload )
	{
		return new SyncJob( $payload );
	}

	/**
	 * Raise the before queue job event.
	 *
	 * @param  Job $job
	 * @return void
	 */
	protected function raiseBeforeJobEvent( Job $job )
	{
		$data = json_decode( $job->getRawBody(), true );

		Hooks::trigger( 'queue.job.processing', compact( 'job', 'data' ) );
	}

	/**
	 * Raise the after queue job event.
	 *
	 * @param  Job $job
	 * @return void
	 */
	protected function raiseAfterJobEvent( Job $job )
	{
		$data = json_decode( $job->getRawBody(), true );

		Hooks::trigger( 'queue.sync', compact( 'job', 'data' ) );
	}

	/**
	 * Raise the exception occurred queue job event.
	 *
	 * @param  Job $job
	 * @param  \Throwable $exception
	 * @return void
	 */
	protected function raiseExceptionOccurredJobEvent( Job $job, $exception )
	{
		$data = json_decode( $job->getRawBody(), true );

		Hooks::trigger( 'queue.job.exception', compact( 'job', 'data', 'exception' ) );
	}

	/**
	 * Handle the failed job.
	 *
	 * @param  Job $job
	 * @return array
	 */
	protected function handleFailedJob( Job $job )
	{
		$job->failed();

		$this->raiseFailedJobEvent( $job );
	}

	/**
	 * Raise the failed queue job event.
	 *
	 * @param  Job $job
	 * @return void
	 */
	protected function raiseFailedJobEvent( Job $job )
	{
		$data = json_decode( $job->getRawBody(), true );

		Hooks::trigger( 'queue.job.failed', compact( 'job', 'data' ) );
	}
}
