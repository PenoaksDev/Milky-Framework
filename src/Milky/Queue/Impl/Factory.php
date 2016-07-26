<?php namespace Milky\Queue\Impl;

interface Factory
{
	/**
	 * Resolve a queue connection instance.
	 *
	 * @param  string  $name
	 * @return Queue
	 */
	public function connection($name = null);
}
