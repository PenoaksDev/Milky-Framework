<?php namespace Milky;

use Milky\Exceptions\FrameworkException;
use Milky\Helpers\Arr;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
trait Globals
{
	/**
	 * Global Binds
	 *
	 * @var array
	 */
	private static $globals = [];

	/**
	 * Binding aliases
	 *
	 * @var array
	 */
	private static $aliases = [];

	/**
	 * @param $key
	 * @param string|array $aliases
	 */
	public function addAlias( $aliases, $key )
	{
		if ( !is_array( $aliases ) )
			$aliases = [$aliases];

		foreach ( $aliases as $alias )
			static::$aliases[$alias] = $key;
	}

	public function offsetExists( $key )
	{
		return Arr::exists( static::$globals, $key );
	}

	public function offsetGet( $key )
	{
		return static::get( $key );
	}

	public function offsetSet( $key, $value )
	{
		static::set( $key, $value );
	}

	public function offsetUnset( $key )
	{
		Arr::forget( static::$globals, $key );
	}

	public function __get( $key )
	{
		return static::get( $key );
	}

	public function __set( $key, $value )
	{
		static::set( $key, $value );
	}

	public static function get( $key )
	{
		if ( array_key_exists( $key, static::$aliases ) )
			$key = static::$aliases[$key];

		$value = Arr::get( static::$globals, $key );

		if ( is_null( $value ) )
		{
			static::fw()->hooks->trigger( 'binding.failed', $key );

			$value = Arr::get( static::$globals, $key );
			if ( is_null( $value ) )
				throw new FrameworkException( "There is no binding available for key [" . $key . "]" );
		}

		return $value;
	}

	public static function set( $key, $value )
	{
		Arr::set( static::$globals, $key, $value );
	}
}
