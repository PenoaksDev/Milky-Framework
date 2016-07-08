<?php

namespace Penoaks\Support\Facades;

/**
 * @see \Penoaks\Database\DatabaseManager
 * @see \Penoaks\Database\Connection
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
