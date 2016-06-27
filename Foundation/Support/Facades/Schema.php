<?php

namespace Foundation\Support\Facades;

/**
 * @see \Foundation\Database\Schema\Builder
 */
class Schema extends Facade
{
	/**
	 * Get a schema builder instance for a connection.
	 *
	 * @param  string  $name
	 * @return \Foundation\Database\Schema\Builder
	 */
	public static function connection($name)
	{
		return static::$app['db']->connection($name)->getSchemaBuilder();
	}

	/**
	 * Get a schema builder instance for the default connection.
	 *
	 * @return \Foundation\Database\Schema\Builder
	 */
	protected static function getFacadeAccessor()
	{
		return static::$app['db']->connection()->getSchemaBuilder();
	}
}
