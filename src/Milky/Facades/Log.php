<?php namespace Milky\Facades;

use Milky\Logging\Logger;

/**
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class Log extends BaseFacade
{
	public function __getResolver()
	{
		return Logger::class;
	}

	/**
	 * Logging an emergency message to the logs.
	 *
	 * @param  string $message
	 * @param  array $context
	 */
	public static function emergency( $message, array $context = [] )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Logging an alert message to the logs.
	 *
	 * @param  string $message
	 * @param  array $context
	 */
	public static function alert( $message, array $context = [] )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Logging a critical message to the logs.
	 *
	 * @param  string $message
	 * @param  array $context
	 */
	public static function critical( $message, array $context = [] )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Logging an error message to the logs.
	 *
	 * @param  string $message
	 * @param  array $context
	 */
	public static function error( $message, array $context = [] )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Logging a warning message to the logs.
	 *
	 * @param  string $message
	 * @param  array $context
	 */
	public static function warning( $message, array $context = [] )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Logging a notice to the logs.
	 *
	 * @param  string $message
	 * @param  array $context
	 */
	public static function notice( $message, array $context = [] )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Logging an informational message to the logs.
	 *
	 * @param  string $message
	 * @param  array $context
	 */
	public static function info( $message, array $context = [] )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Logging a debug message to the logs.
	 *
	 * @param  string $message
	 * @param  array $context
	 */
	public static function debug( $message, array $context = [] )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Logging a message to the logs.
	 *
	 * @param  string $level
	 * @param  string $message
	 * @param  array $context
	 */
	public static function log( $level, $message, array $context = [] )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Dynamically pass log calls into the writer.
	 *
	 * @param  string $level
	 * @param  string $message
	 * @param  array $context
	 */
	public static function write( $level, $message, array $context = [] )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Write a message to Monolog.
	 *
	 * @param  string $level
	 * @param  string $message
	 * @param  array $context
	 */
	protected static function writeLog( $level, $message, $context )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}
}
