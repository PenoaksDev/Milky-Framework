<?php

namespace Foundation\Support\Facades;

/**
 * @see \Foundation\Database\DatabaseManager
 * @see \Foundation\Database\Connection
 */
class DB extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'db';
	}
}
