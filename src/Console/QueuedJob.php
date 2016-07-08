<?php

namesapce Penoaks\Console;

use Foundation\Contracts\Console\Kernel as KernelContract;

class QueuedJob
{
	/**
	 * The kernel instance.
	 *
	 * @var \Penoaks\Contracts\Console\Kernel
	 */
	protected $kernel;

	/**
	 * Create a new job instance.
	 *
	 * @param  \Penoaks\Contracts\Console\Kernel  $kernel
	 * @return void
	 */
	public function __construct(KernelContract $kernel)
	{
		$this->kernel = $kernel;
	}

	/**
	 * Fire the job.
	 *
	 * @param  \Penoaks\Queue\Jobs\Job  $job
	 * @param  array  $data
	 * @return void
	 */
	public function fire($job, $data)
	{
		call_user_func_array([$this->kernel, 'call'], $data);

		$job->delete();
	}
}
