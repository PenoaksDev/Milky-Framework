<?php

namespace Foundation\Support\Facades;

/**
 * @see \Foundation\Queue\QueueManager
 * @see \Foundation\Queue\Queue
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
