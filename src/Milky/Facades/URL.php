<?php namespace Milky\Facades;

use Milky\Database\Eloquent\RoutableModel;
use Milky\Http\Routing\RouteCollection;
use Milky\Http\Routing\UrlGenerator;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class URL extends BaseFacade
{
	protected function __getResolver()
	{
		return UrlGenerator::class;
	}

	public static function full()
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function current()
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function previous( $fallback = false )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function to( $path, $extra = [], $secure = null )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function secure( $path, $parameters = [] )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function asset( $path, $secure = null )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function assetFrom( $root, $path, $secure = null )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function secureAsset( $path )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function forceSchema( $schema )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Get the URL to a named route.
	 *
	 * @param string $name
	 * @param RoutableModel $model
	 * @param mixed $parameters
	 * @param bool $absolute
	 * @return string
	 *
	 * @throws RouteNotFoundException
	 */
	public static function routeModel( $name, $model, $parameters = [], $absolute = true )
	{
		return static::__do( __FUNCTION__, compact('name', 'model', 'parameters', 'absolute') );
	}

	/**
	 * Get the URL to a named route.
	 *
	 * @param  string $name
	 * @param  mixed $parameters
	 * @param  bool $absolute
	 * @return string
	 *
	 * @throws RouteNotFoundException
	 */
	public static function route( $name, $parameters = [], $absolute = true )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function action( $action, $parameters = [], $absolute = true )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function forceRootUrl( $root )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function isValidUrl( $path )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function getRequest()
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function setRequest( Request $request )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function setRoutes( RouteCollection $routes )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function setSessionResolver( callable $sessionResolver )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function setRootControllerNamespace( $rootNamespace )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}
}
