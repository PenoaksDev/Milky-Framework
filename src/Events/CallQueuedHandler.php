<?php
namesapce Penoaks\Events;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */

use Foundation\Barebones\Event;
use Foundation\Contracts\Queue\Job;
use Foundation\Framework;

class CallQueuedHandler implements Event
{
	/**
	 * The bindings instance.
	 *
	 * @var \Penoaks\Framework
	 */
	protected $bindings;

	/**
	 * Create a new job instance.
	 *
	 * @param  \Penoaks\Framework  $bindings
	 * @return void
	 */
	public function __construct(Bindings $bindings)
	{
		$this->bindings = $bindings;
	}

	/**
	 * Handle the queued job.
	 *
	 * @param  \Penoaks\Contracts\Queue\Job  $job
	 * @param  array  $data
	 * @return void
	 */
	public function call(Job $job, array $data)
	{
		$handler = $this->setJobInstanceIfNecessary(
			$job, $this->bindings->make($data['class'])
		);

		call_user_func_array(
			[$handler, $data['method']], unserialize($data['data'])
		);

		if (! $job->isDeletedOrReleased())
{
			$job->delete();
		}
	}

	/**
	 * Set the job instance of the given class if necessary.
	 *
	 * @param  \Penoaks\Contracts\Queue\Job  $job
	 * @param  mixed  $instance
	 * @return mixed
	 */
	protected function setJobInstanceIfNecessary(Job $job, $instance)
	{
		if (in_array('Penoaks\Queue\InteractsWithQueue', class_uses_recursive(get_class($instance))))
{
			$instance->setJob($job);
		}

		return $instance;
	}

	/**
	 * Call the failed method on the job instance.
	 *
	 * @param  array  $data
	 * @return void
	 */
	public function failed(array $data)
	{
		$handler = $this->bindings->make($data['class']);

		if (method_exists($handler, 'failed'))
{
			call_user_func_array([$handler, 'failed'], unserialize($data['data']));
		}
	}
}
