<?php

namesapce Penoaks\Support\Facades;

/**
 * @see \Penoaks\Database\Schema\Builder
 */
class Schema extends Facade
{
	/**
	 * Get a schema builder instance for a connection.
	 *
	 * @param  string  $name
	 * @return \Penoaks\Database\Schema\Builder
	 */
	public static function connection($name)
	{
		return static::$fw->bindings['db']->connection($name)->getSchemaBuilder();
	}

	/**
	 * Get a schema builder instance for the default connection.
	 *
	 * @return \Penoaks\Database\Schema\Builder
	 */
	protected static function getFacadeAccessor()
	{
		return static::$fw->bindings['db']->connection()->getSchemaBuilder();
	}
}
