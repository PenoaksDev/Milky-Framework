<?php

namespace Foundation\Contracts\Queue;

interface Factory
{
	/**
	 * Resolve a queue connection instance.
	 *
	 * @param  string  $name
	 * @return \Foundation\Contracts\Queue\Queue
	 */
	public function connection($name = null);
}
