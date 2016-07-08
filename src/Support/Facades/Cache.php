<?php

namespace Penoaks\Support\Facades;

/**
 * @see \Penoaks\Cache\CacheManager
 * @see \Penoaks\Cache\Repository
 */
class Cache extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'cache';
	}
}
