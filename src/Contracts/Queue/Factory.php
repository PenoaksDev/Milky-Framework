<?php

namesapce Penoaks\Contracts\Queue;

interface Factory
{
	/**
	 * Resolve a queue connection instance.
	 *
	 * @param  string  $name
	 * @return \Penoaks\Contracts\Queue\Queue
	 */
	public function connection($name = null);
}
