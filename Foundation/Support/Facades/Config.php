<?php

namespace Foundation\Support\Facades;

/**
 * @see \Foundation\Config\Repository
 */
class Config extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'config';
	}
}
