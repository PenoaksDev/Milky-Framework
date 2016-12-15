<?php namespace Milky\Facades;

use Milky\Http\Routing\ResponseFactory;

/**
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class Response extends BaseFacade
{
	protected function __getResolver()
	{
		return ResponseFactory::class;
	}

	public static function make( $content = '', $status = 200, array $headers = [] )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function view( $view, $data = [], $status = 200, array $headers = [] )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function json( $data = [], $status = 200, array $headers = [], $options = 0 )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function jsonp( $callback, $data = [], $status = 200, array $headers = [], $options = 0 )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function stream( $callback, $status = 200, array $headers = [] )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function download( $file, $name = null, array $headers = [], $disposition = 'attachment' )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function file( $file, array $headers = [] )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function redirectTo( $path, $status = 302, $headers = [], $secure = null )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function redirectToRoute( $route, $parameters = [], $status = 302, $headers = [] )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function redirectToAction( $action, $parameters = [], $status = 302, $headers = [] )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function redirectGuest( $path, $status = 302, $headers = [], $secure = null )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function redirectToIntended( $default = '/', $status = 302, $headers = [], $secure = null )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}
}
