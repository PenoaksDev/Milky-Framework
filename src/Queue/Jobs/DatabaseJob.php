<?php

namespace Penoaks\Queue\Jobs;

use Penoaks\Queue\DatabaseQueue;
use Penoaks\Framework;
use Penoaks\Contracts\Queue\Job as JobContract;

class DatabaseJob extends Job implements JobContract
{
	/**
	 * The database queue instance.
	 *
	 * @var \Penoaks\Queue\DatabaseQueue
	 */
	protected $database;

	/**
	 * The database job payload.
	 *
	 * @var \StdClass
	 */
	protected $job;

	/**
	 * Create a new job instance.
	 *
	 * @param  \Penoaks\Framework  $bindings
	 * @param  \Penoaks\Queue\DatabaseQueue  $database
	 * @param  \StdClass  $job
	 * @param  string  $queue
	 * @return void
	 */
	public function __construct(Bindings $bindings, DatabaseQueue $database, $job, $queue)
	{
		$this->job = $job;
		$this->queue = $queue;
		$this->database = $database;
		$this->bindings = $bindings;
		$this->job->attempts = $this->job->attempts + 1;
	}

	/**
	 * Fire the job.
	 *
	 * @return void
	 */
	public function fire()
	{
		$this->resolveAndFire(json_decode($this->job->payload, true));
	}

	/**
	 * Delete the job from the queue.
	 *
	 * @return void
	 */
	public function delete()
	{
		parent::delete();

		$this->database->deleteReserved($this->queue, $this->job->id);
	}

	/**
	 * Release the job back into the queue.
	 *
	 * @param  int  $delay
	 * @return void
	 */
	public function release($delay = 0)
	{
		parent::release($delay);

		$this->delete();

		$this->database->release($this->queue, $this->job, $delay);
	}

	/**
	 * Get the number of times the job has been attempted.
	 *
	 * @return int
	 */
	public function attempts()
	{
		return (int) $this->job->attempts;
	}

	/**
	 * Get the job identifier.
	 *
	 * @return string
	 */
	public function getJobId()
	{
		return $this->job->id;
	}

	/**
	 * Get the raw body string for the job.
	 *
	 * @return string
	 */
	public function getRawBody()
	{
		return $this->job->payload;
	}

	/**
	 * Get the IoC bindings instance.
	 *
	 * @return \Penoaks\Framework
	 */
	public function getBindings()
	{
		return $this->bindings;
	}

	/**
	 * Get the underlying queue driver instance.
	 *
	 * @return \Penoaks\Queue\DatabaseQueue
	 */
	public function getDatabaseQueue()
	{
		return $this->database;
	}

	/**
	 * Get the underlying database job.
	 *
	 * @return \StdClass
	 */
	public function getDatabaseJob()
	{
		return $this->job;
	}
}
