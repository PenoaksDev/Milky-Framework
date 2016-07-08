<?php

namespace Penoaks\Support\Facades;

/**
 * @see \Penoaks\Queue\QueueManager
 * @see \Penoaks\Queue\Queue
 */
class Queue extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'queue';
	}
}
