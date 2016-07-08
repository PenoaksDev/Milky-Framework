<?php
namesapce Penoaks\Traits;

use Foundation\Bindings\Bindings;
use Foundation\Framework;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
trait StaticAccess
{
	/**
	 * Holds the self instance.
	 * It's recommended that you do "static::$selfInstance = $this;" in your class __constructor
	 *
	 * @var self
	 */
	private static $selfInstance;

	/**
	 * Handle dynamic, static calls to the object.
	 *
	 * @param  string $method
	 * @param  array $args
	 * @return mixed
	 *
	 * @throws \RuntimeException
	 */
	public static function __callStatic( $method, $args )
	{
		if ( !isset( static::$selfInstance ) )
			static::$selfInstance = Bindings::i()->get( __CLASS__ );

		if ( str_contains( $method, '::' ) )
			$method = substr( $method, strpos( $method, '::' ) + 2 );

		if( !method_exists( static::$selfInstance, $method ) )
			throw new \RuntimeException( "Non-static method [" . $method . "] does not exist for class [" . __CLASS__ . "]" );

		return call_user_func_array( [static::$selfInstance, $method], $args );
	}

	/**
	 * Used to check if a static call was made on a non-static method, e.g.
	 * if ( static::wasStatic() )
	 *      return static::__callStatic( __METHOD__, func_get_args() );
	 *
	 * @return bool
	 */
	private static function wasStatic()
	{
		return debug_backtrace()[1]['type'] == '::';
	}

	/**
	 * @return self
	 */
	public static function i()
	{
		if ( __CLASS__ != "Penoaks\\Bindings\\Bindings" && is_null ( static::$selfInstance ) )
			Bindings::i()->get( __CLASS__ );

		return static::$selfInstance;
	}
}
