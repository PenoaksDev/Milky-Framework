<?php
namespace Penoaks\Barebones;

use Penoaks\Support\Arr;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
abstract class BaseArray implements \ArrayAccess
{
	/**
	 * @var array
	 */
	protected $arr = [];

	/**
	 * @param $key
	 * @param null $value
	 */
	public function add( $key, $value = null )
	{
		if ( is_array( $key ) )
		{
			if ( $this->onCall( 'addAll', null, $key ) !== true )
				$this->arr = array_merge_recursive( $this->arr, $key );
		}
		else if ( is_null( $value ) )
		{
			$value = $key;
			$key = 0;
			while ( isset( $this->arr[$key] ) )
			{
				$key++;
			}
			if ( $this->onCall( 'add', $key, $value ) !== true )
				Arr::set( $this->arr, $key, $value );
		}
		else
		{
			if ( $this->onCall( 'add', $key, $value ) !== true )
				Arr::set( $this->arr, $key, $value );
		}
	}

	/**
	 * Clear array of it's contents
	 */
	public function clear()
	{
		if ( $this->onCall( 'clear', null ) !== true )
			$this->arr = [];
	}

	/**
	 * @param $key
	 */
	public function unset( $key )
	{
		if ( $this->onCall( 'unset', $key ) !== true )
			Arr::forget( $this->arr, $key );
	}

	/**
	 * @param $key
	 * @return mixed
	 */
	public function get( $key )
	{
		$this->onCall( 'get', $key );

		return Arr::get( $this->arr, $key );
	}

	/**
	 * @param $key
	 * @return bool
	 */
	public function has( $key )
	{
		$this->onCall( 'has', $key );

		return Arr::has( $this->arr, $key );
	}

	/**
	 * Internal method for calling events
	 *
	 * @param $method
	 * @param $key
	 * @param null $value
	 */
	protected abstract function onCall( $method, $key, &$value = null );

	/**
	 * Get property
	 *
	 * @param $key
	 * @return mixed
	 */
	public function __get( $key )
	{
		return $this->get( $key );
	}

	/**
	 * Set property
	 *
	 * @param $key
	 * @param $value
	 */
	public function __set( $key, $value )
	{
		$this->add( $key, $value );
	}

	/**
	 * Whether a offset exists
	 *
	 * @param mixed $offset
	 * @return bool
	 */
	public function offsetExists( $key )
	{
		return $this->has( $key );
	}

	/**
	 * Offset to retrieve
	 *
	 * @param mixed $offset
	 * @return mixed
	 */
	public function offsetGet( $key )
	{
		return $this->get( $key );
	}

	/**
	 * Offset to set
	 *
	 * @param mixed $offset
	 */
	public function offsetSet( $key, $value = null )
	{
		$this->add( $key, $value );
	}

	/**
	 * Offset to unset
	 *
	 * @param mixed $offset
	 */
	public function offsetUnset( $key )
	{
		$this->unset( $key );
	}
}
