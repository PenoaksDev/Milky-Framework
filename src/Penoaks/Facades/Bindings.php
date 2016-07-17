<?php
namespace Penoaks\Facades;

use Closure;
use Penoaks\Bindings\Bindings as Scaffold;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class Bindings extends BaseFacade
{
	protected function __getResolver()
	{
		return Scaffold::i();
	}

	public static function when( $concrete )
	{
		static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function bound( $abstract )
	{
		static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function resolved( $abstract )
	{
		static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function isAlias( $name )
	{
		static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function bind( $abstract, $concrete = null, $shared = false, array $aliases = [] )
	{
		static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function addContextualBinding( $concrete, $abstract, $implementation )
	{
		static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function bindIf( $abstract, $concrete = null, $shared = false )
	{
		static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function singleton( $abstract, $concrete = null, array $aliases = [] )
	{
		static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function share( Closure $closure )
	{
		static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function extend( $abstract, Closure $closure )
	{
		static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function instance( $abstract, $instance, array $aliases = [] )
	{
		static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function tag( $abstracts, $tags )
	{
		static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function tagged( $tag )
	{
		static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function alias( $abstract, $alias )
	{
		static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function rebinding( $abstract, Closure $callback )
	{
		static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function refresh( $abstract, $target, $method )
	{
		static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function wrap( Closure $callback, array $parameters = [] )
	{
		static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function call( $callback, array $parameters = [], $defaultMethod = null )
	{
		static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function make( $abstract, array $parameters = [] )
	{
		static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function build( $concrete, array $parameters = [] )
	{
		static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function getDependencies( array $parameters, array $primitives = [], $classAndMethod = null )
	{
		static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function keyParametersByArgument( array $dependencies, array $parameters )
	{
		static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function resolving( $abstract, Closure $callback = null )
	{
		static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function afterResolving( $abstract, Closure $callback = null )
	{
		static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function isShared( $abstract )
	{
		static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function getAlias( $abstract )
	{
		static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function getBindings()
	{
		static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function forgetInstance( $abstract )
	{
		static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function forgetInstances()
	{
		static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function flush()
	{
		static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function get( $key )
	{
		static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}
}
