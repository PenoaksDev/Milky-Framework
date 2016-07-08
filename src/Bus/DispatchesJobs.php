<?php

namesapce Penoaks\Bus;

use Foundation\Contracts\Bus\Dispatcher;

trait DispatchesJobs
{
	/**
	 * Dispatch a job to its appropriate handler.
	 *
	 * @param  mixed  $job
	 * @return mixed
	 */
	protected function dispatch($job)
	{
		return fw(Dispatcher::class)->dispatch($job);
	}

	/**
	 * Dispatch a command to its appropriate handler in the current process.
	 *
	 * @param  mixed  $job
	 * @return mixed
	 */
	public function dispatchNow($job)
	{
		return fw(Dispatcher::class)->dispatchNow($job);
	}
}
