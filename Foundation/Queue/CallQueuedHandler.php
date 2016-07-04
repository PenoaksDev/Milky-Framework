<?php

namespace Foundation\Queue;

use Foundation\Contracts\Queue\Job;
use Foundation\Contracts\Bus\Dispatcher;

class CallQueuedHandler
{
	/**
	 * The bus dispatcher implementation.
	 *
	 * @var \Foundation\Contracts\Bus\Dispatcher
	 */
	protected $dispatcher;

	/**
	 * Create a new handler instance.
	 *
	 * @param  \Foundation\Contracts\Bus\Dispatcher  $dispatcher
	 * @return void
	 */
	public function __construct(Dispatcher $dispatcher)
	{
		$this->dispatcher = $dispatcher;
	}

	/**
	 * Handle the queued job.
	 *
	 * @param  \Foundation\Contracts\Queue\Job  $job
	 * @param  array  $data
	 * @return void
	 */
	public function call(Job $job, array $data)
	{
		$command = $this->setJobInstanceIfNecessary(
			$job, unserialize($data['command'])
		);

		$this->dispatcher->dispatchNow($command);

		if (! $job->isDeletedOrReleased())
{
			$job->delete();
		}
	}

	/**
	 * Set the job instance of the given class if necessary.
	 *
	 * @param  \Foundation\Contracts\Queue\Job  $job
	 * @param  mixed  $instance
	 * @return mixed
	 */
	protected function setJobInstanceIfNecessary(Job $job, $instance)
	{
		if (in_array('Foundation\Queue\InteractsWithQueue', class_uses_recursive(get_class($instance))))
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
		$command = unserialize($data['command']);

		if (method_exists($command, 'failed'))
{
			$command->failed();
		}
	}
}
