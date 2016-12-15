<?php namespace Milky\Binding;

use Milky\Exceptions\BindingException;
use Milky\Framework;
use Milky\Helpers\Arr;

/**
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 *
 * @deprecated replaced by ServiceFactory and ServiceResolver
 * Still need to consider how to storm simple values for later use, maybe another ServiceFactory or Facade?
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

	public static function available( $key )
	{
		return array_key_exists( $key, static::$aliases ) || Arr::exists( static::$globals, $key );
	}

	/**
	 * Gets globals by their final class
	 *
	 * @param string $class
	 * @return array
	 */
	public static function getByClass( $class )
	{
		$results = [];
		foreach ( static::$globals as $k => $v )
			if ( $v instanceof $class )
				$results[$k] = $v;
		return $results;
	}

	public static function get( $key )
	{
		if ( array_key_exists( $key, static::$aliases ) )
			$key = static::$aliases[$key];

		$value = Arr::get( static::$globals, $key );

		if ( is_null( $value ) )
		{
			/**
			 * As an alternative, we will check with the Service Resolvers for an instance.
			 */
			$value = UniversalBuilder::resolve( $key );

			if ( is_null( $value ) )
				throw new BindingException( "There is no binding available for key [" . $key . "]" );
		}

		if ( is_callable( $value ) || $value instanceof \Closure )
			return UniversalBuilder::call( $value );
		return $value;
	}

	public static function set( $key, $value )
	{
		Arr::setWithException( static::$globals, $key, $value );
	}
}
