<?php
namespace Penoaks\Config;

use ArrayAccess;
use Penoaks\Support\Arr;

class Config implements ArrayAccess
{
	use \Penoaks\Traits\StaticAccess;

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
	 * @return void
	 */
	public function __construct( array $items = [] )
	{
		static::$selfInstance = $this;
		$this->items = $items;
	}

	/**
	 * Determine if the given configuration value exists.
	 *
	 * @param  string $key
	 * @return bool
	 */
	public static function has( $key )
	{
		return Arr::has( static::i()->items, $key );
	}

	/**
	 * Get the specified configuration value.
	 *
	 * @param  string $key
	 * @param  mixed $default
	 * @return mixed
	 */
	public static function get( $key, $default = null )
	{
		return Arr::get( static::i()->items, $key, $default );
	}

	/**
	 * Set a given configuration value.
	 *
	 * @param  array|string $key
	 * @param  mixed $value
	 */
	public function set( $key, $value = null )
	{
		if ( static::wasStatic() )
			static::__callStatic( __METHOD__, func_get_args() );
		else if ( is_array( $key ) )
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
		if ( static::wasStatic() )
			static::__callStatic( __METHOD__, func_get_args() );
		else
		{
			$array = $this->get( $key );
			array_unshift( $array, $value );
			$this->set( $key, $array );
		}
	}

	/**
	 * Push a value onto an array configuration value.
	 *
	 * @param  string $key
	 * @param  mixed $value
	 */
	public function push( $key, $value )
	{
		if ( static::wasStatic() )
			static::__callStatic( __METHOD__, func_get_args() );
		else
		{
			$array = $this->get( $key );
			$array[] = $value;
			$this->set( $key, $array );
		}
	}

	/**
	 * Get all of the configuration items for the application.
	 *
	 * @return array
	 */
	public function all()
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );
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
	 * @return void
	 */
	public function offsetSet( $key, $value )
	{
		$this->set( $key, $value );
	}

	/**
	 * Unset a configuration option.
	 *
	 * @param  string $key
	 * @return void
	 */
	public function offsetUnset( $key )
	{
		$this->set( $key, null );
	}
}
