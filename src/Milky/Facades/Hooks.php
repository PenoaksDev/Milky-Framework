<?php namespace Milky\Facades;

use Milky\Framework;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class Hooks extends BaseFacade
{
	protected function __getResolver()
	{
		return Framework::hooks();
	}

	/**
	 * Adds Hook
	 *
	 * @param array $triggers
	 * @param callable $callable
	 * @param string $name
	 */
	public static function addHook( array $triggers, callable $callable, $key = null )
	{
		static::__do( __FUNCTION__, compact( 'triggers', 'callable', 'key' ) );
	}

	/**
	 * Remove Hook
	 *
	 * @param $key
	 */
	public static function removeHooks( $key )
	{
		static::__do( __FUNCTION__, compact( 'key' ) );
	}

	/**
	 * Triggers Hooks
	 *
	 * @param $trigger
	 */
	public static function trigger( $trigger, $params = [] )
	{
		static::__do( __FUNCTION__, compact( 'trigger', 'params' ) );
	}
}
