<?php

namespace Foundation\Support\Facades;

/**
 * @see \Foundation\Cache\CacheManager
 * @see \Foundation\Cache\Repository
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
