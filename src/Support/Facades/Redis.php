<?php

namespace Penoaks\Support\Facades;

/**
 * @see \Penoaks\Redis\Database
 */
class Redis extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'redis';
	}
}
