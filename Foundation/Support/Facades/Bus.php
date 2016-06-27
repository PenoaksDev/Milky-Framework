<?php

namespace Foundation\Support\Facades;

/**
 * @see \Foundation\Contracts\Bus\Dispatcher
 */
class Bus extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'Foundation\Contracts\Bus\Dispatcher';
	}
}
