<?php

namespace Foundation\Console;

use Foundation\Contracts\Console\Kernel as KernelContract;

class QueuedJob
{
	/**
	 * The kernel instance.
	 *
	 * @var \Foundation\Contracts\Console\Kernel
	 */
	protected $kernel;

	/**
	 * Create a new job instance.
	 *
	 * @param  \Foundation\Contracts\Console\Kernel  $kernel
	 * @return void
	 */
	public function __construct(KernelContract $kernel)
	{
		$this->kernel = $kernel;
	}

	/**
	 * Fire the job.
	 *
	 * @param  \Foundation\Queue\Jobs\Job  $job
	 * @param  array  $data
	 * @return void
	 */
	public function fire($job, $data)
	{
		call_user_func_array([$this->kernel, 'call'], $data);

		$job->delete();
	}
}
