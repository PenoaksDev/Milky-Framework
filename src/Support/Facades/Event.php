<?php

namespace Penoaks\Support\Facades;

/**
 * @see \Penoaks\Events\Dispatcher
 */
class Event extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'events';
	}
}
