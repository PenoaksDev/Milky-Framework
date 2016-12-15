<?php namespace Milky\Facades;

use Milky\Http\RedirectResponse;
use Milky\Http\Routing\Redirector;

/**
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class Redirect extends BaseFacade
{
	protected function __getResolver()
	{
		return Redirector::class;
	}

	/**
	 * Create a new redirect response to the "home" route.
	 *
	 * @param  int $status
	 * @return RedirectResponse
	 */
	public static function home( $status = 302 )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Create a new redirect response to the previous location.
	 *
	 * @param  int $status
	 * @param  array $headers
	 * @return RedirectResponse
	 */
	public static function back( $status = 302, $headers = [] )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Create a new redirect response to the current URI.
	 *
	 * @param  int $status
	 * @param  array $headers
	 * @return RedirectResponse
	 */
	public static function refresh( $status = 302, $headers = [] )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Create a new redirect response, while putting the current URL in the session.
	 *
	 * @param  string $path
	 * @param  int $status
	 * @param  array $headers
	 * @param  bool $secure
	 * @return RedirectResponse
	 */
	public static function guest( $path, $status = 302, $headers = [], $secure = null )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Create a new redirect response to the previously intended location.
	 *
	 * @param  string $default
	 * @param  int $status
	 * @param  array $headers
	 * @param  bool $secure
	 * @return RedirectResponse
	 */
	public static function intended( $default = '/', $status = 302, $headers = [], $secure = null )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Create a new redirect response to the given path.
	 *
	 * @param  string $path
	 * @param  int $status
	 * @param  array $headers
	 * @param  bool $secure
	 * @return RedirectResponse
	 */
	public static function to( $path, $status = 302, $headers = [], $secure = null )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Create a new redirect response to an external URL (no validation).
	 *
	 * @param  string $path
	 * @param  int $status
	 * @param  array $headers
	 * @return RedirectResponse
	 */
	public static function away( $path, $status = 302, $headers = [] )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Create a new redirect response to the given HTTPS path.
	 *
	 * @param  string $path
	 * @param  int $status
	 * @param  array $headers
	 * @return RedirectResponse
	 */
	public static function secure( $path, $status = 302, $headers = [] )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Create a new redirect response to a named route.
	 *
	 * @param  string $route
	 * @param  array $parameters
	 * @param  int $status
	 * @param  array $headers
	 * @return RedirectResponse
	 */
	public static function route( $route, $parameters = [], $status = 302, $headers = [] )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Create a new redirect response to a controller action.
	 *
	 * @param  string $action
	 * @param  array $parameters
	 * @param  int $status
	 * @param  array $headers
	 * @return RedirectResponse
	 */
	public static function action( $action, $parameters = [], $status = 302, $headers = [] )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}
}
