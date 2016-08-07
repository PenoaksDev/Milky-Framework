<?php namespace Milky\Config;

use ArrayAccess;
use Milky\Helpers\Arr;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class Configuration implements ArrayAccess
{
	/**
	 * All of the configuration items.
	 *
	 * @var array
	 */
	protected $items = [];

	/**
	 * Create a new configuration repository.
	 *
	 * @param  array $items
	 */
	public function __construct( array $items = [] )
	{
		$this->items = $items;
	}

	/**
	 * Determine if the given configuration value exists.
	 *
	 * @param  string $key
	 * @return bool
	 */
	public function has( $key )
	{
		return Arr::has( $this->items, $key );
	}

	/**
	 * Get the specified configuration value.
	 *
	 * @param  string $key
	 * @param  mixed $default
	 * @return mixed
	 */
	public function get( $key, $default = null )
	{
		return Arr::get( $this->items, $key, $default );
	}

	/**
	 * Set a given configuration value.
	 *
	 * @param  array|string $key
	 * @param  mixed $value
	 */
	public function set( $key, $value = null )
	{
		if ( is_array( $key ) )
			foreach ( $key as $innerKey => $innerValue )
				Arr::set( $this->items, $innerKey, $innerValue );
		else
			Arr::set( $this->items, $key, $value );
	}

	/**
	 * Prepend a value onto an array configuration value.
	 *
	 * @param  string $key
	 * @param  mixed $value
	 */
	public function prepend( $key, $value )
	{
		$array = $this->get( $key );
		array_unshift( $array, $value );
		$this->set( $key, $array );
	}

	/**
	 * Push a value onto an array configuration value.
	 *
	 * @param  string $key
	 * @param  mixed $value
	 */
	public function push( $key, $value )
	{
		$array = $this->get( $key );
		$array[] = $value;
		$this->set( $key, $array );
	}

	/**
	 * Get all of the configuration items for the application.
	 *
	 * @return array
	 */
	public function all()
	{
		return $this->items;
	}

	/**
	 * Determine if the given configuration option exists.
	 *
	 * @param  string $key
	 * @return bool
	 */
	public function offsetExists( $key )
	{
		return $this->has( $key );
	}

	/**
	 * Get a configuration option.
	 *
	 * @param  string $key
	 * @return mixed
	 */
	public function offsetGet( $key )
	{
		return $this->get( $key );
	}

	/**
	 * Set a configuration option.
	 *
	 * @param  string $key
	 * @param  mixed $value
	 */
	public function offsetSet( $key, $value )
	{
		$this->set( $key, $value );
	}

	/**
	 * Unset a configuration option.
	 *
	 * @param  string $key
	 */
	public function offsetUnset( $key )
	{
		$this->set( $key, null );
	}
}
