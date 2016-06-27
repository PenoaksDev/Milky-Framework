<?php

namespace Foundation\Queue\Jobs;

use Foundation\Support\Arr;
use Foundation\Queue\RedisQueue;
use Foundation\Container\Container;
use Foundation\Contracts\Queue\Job as JobContract;

class RedisJob extends Job implements JobContract
{
	/**
	 * The Redis queue instance.
	 *
	 * @var \Foundation\Queue\RedisQueue
	 */
	protected $redis;

	/**
	 * The Redis job payload.
	 *
	 * @var string
	 */
	protected $job;

	/**
	 * Create a new job instance.
	 *
	 * @param  \Foundation\Container\Container  $container
	 * @param  \Foundation\Queue\RedisQueue  $redis
	 * @param  string  $job
	 * @param  string  $queue
	 * @return void
	 */
	public function __construct(Container $container, RedisQueue $redis, $job, $queue)
	{
		$this->job = $job;
		$this->redis = $redis;
		$this->queue = $queue;
		$this->container = $container;
	}

	/**
	 * Fire the job.
	 *
	 * @return void
	 */
	public function fire()
	{
		$this->resolveAndFire(json_decode($this->getRawBody(), true));
	}

	/**
	 * Get the raw body string for the job.
	 *
	 * @return string
	 */
	public function getRawBody()
	{
		return $this->job;
	}

	/**
	 * Delete the job from the queue.
	 *
	 * @return void
	 */
	public function delete()
	{
		parent::delete();

		$this->redis->deleteReserved($this->queue, $this->job);
	}

	/**
	 * Release the job back into the queue.
	 *
	 * @param  int   $delay
	 * @return void
	 */
	public function release($delay = 0)
	{
		parent::release($delay);

		$this->delete();

		$this->redis->release($this->queue, $this->job, $delay, $this->attempts() + 1);
	}

	/**
	 * Get the number of times the job has been attempted.
	 *
	 * @return int
	 */
	public function attempts()
	{
		return Arr::get(json_decode($this->job, true), 'attempts');
	}

	/**
	 * Get the job identifier.
	 *
	 * @return string
	 */
	public function getJobId()
	{
		return Arr::get(json_decode($this->job, true), 'id');
	}

	/**
	 * Get the IoC container instance.
	 *
	 * @return \Foundation\Container\Container
	 */
	public function getContainer()
	{
		return $this->container;
	}

	/**
	 * Get the underlying queue driver instance.
	 *
	 * @return \Foundation\Redis\Database
	 */
	public function getRedisQueue()
	{
		return $this->redis;
	}

	/**
	 * Get the underlying Redis job.
	 *
	 * @return string
	 */
	public function getRedisJob()
	{
		return $this->job;
	}
}
