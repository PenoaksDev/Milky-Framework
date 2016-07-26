<?php namespace Milky\Providers;

use Milky\Binding\BindingBuilder;
use Milky\Exceptions\ProviderException;
use Milky\Facades\Log;
use Milky\Framework;
use Milky\Pipeline\Pipeline;
use Milky\Traits\Macroable;
use Psy\Exception\RuntimeException;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class ProviderRepository implements \ArrayAccess
{
	use Macroable;

	/**
	 * Was the providers already booted?
	 *
	 * @var bool
	 */
	private $isBooted = false;

	/**
	 * @var array
	 */
	private $loadedProviders = [];

	/**
	 * @var array
	 */
	private $bootedProviders = [];

	/**
	 * @var array
	 */
	private $aliases = [];

	/**
	 * @param ServiceProvider|string $provider
	 * @param string $key
	 * @throws ProviderException
	 */
	public function register( $provider, $key = null )
	{
		if ( is_string( $provider ) )
		{
			if ( class_exists( $provider ) )
			{
				$key = $provider;
				$provider = new $provider;
			}
			else
			{
				// TODO Load provider from string
				// if ( is_null( $key ) );
				throw new ProviderException( "Not Implemented!" );
			}
		}
		else if ( $provider instanceof ServiceProvider )
			$key = is_null( $key ) || is_numeric( $key ) ? get_class( $provider ) : $key;
		else
			throw new RuntimeException( "Provides must be an instance of string or " . ServiceProvider::class );

		foreach ( $this->loadedProviders as $k => $v )
			if ( $v == $provider || $v instanceof $provider || $k == $key )
				throw new ProviderException( "Service Provider with class [" . get_class( $provider ) . "] or key [" . $key . "] is already registered." );

		$this->loadedProviders[$key] = $provider;

		if ( property_exists( $provider, 'aliases' ) )
		{
			/** @noinspection PhpUndefinedFieldInspection */
			$aliases = $provider->aliases;
			if ( is_array( $aliases ) )
				foreach ( $aliases as $alias )
					$this->aliases[$alias] = $key;
			else
				$this->aliases[$aliases] = $key;
		}

		if ( method_exists( $provider, 'register' ) )
			call_user_func( [$provider, 'register'] );

		Framework::fw()->hooks->trigger( 'provider.register', [$provider] );
		Log::info( "Registered Service Provider [" . get_class( $provider ) . "]" );

		if ( $this->isBooted )
		{
			$this->bootedProviders[] = $key;
			if ( method_exists( $provider, 'boot' ) )
				call_user_func( [$provider, 'boot'] );

			Framework::fw()->hooks->trigger( 'provider.boot', [$provider] );
			Log::info( "Booted Service Provider [" . get_class( $provider ) . "]" );
		}
	}

	public function forget( $key )
	{
		if ( array_key_exists( $this->loadedProviders, $key ) )
		{
			unset( $this->loadedProviders[$key] );
			foreach ( array_keys( $this->aliases, $key ) as $alias )
				unset( $this->aliases[$alias] );
			foreach ( array_keys( $this->bootedProviders, $key ) as $provider )
				unset( $this->bootedProviders[$provider] );
		}
	}

	public function sendThroughProviders( $passable, $method = null, callable $exceptionHandler = null )
	{
		$pipeline = new Pipeline();
		$pipeline->send( $passable );
		if ( !is_null( $method ) )
			$pipeline->via( $method );
		if ( !is_null( $exceptionHandler ) )
			$pipeline->withExceptionHandler( $exceptionHandler );
		$pipeline->through( $this->loadedProviders );

		return $pipeline->then( function ( $passable )
		{
			return $passable;
		} );
	}

	public function boot()
	{
		if ( $this->isBooted )
			throw new \RuntimeException( "Service Providers have already been booted" );
		$this->isBooted = true;

		foreach ( $this->loadedProviders as $key => $provider )
		{
			$this->bootedProviders[] = $key;
			if ( method_exists( $provider, 'boot' ) )
				BindingBuilder::call( [$provider, 'boot'] );

			Framework::fw()->hooks->trigger( 'provider.boot', [$provider] );
			Log::info( "Booted Service Provider [" . get_class( $provider ) . "]" );
		}
	}

	public function offsetExists( $key )
	{
		return array_key_exists( $this->loadedProviders, $key ) || in_array( $this->loadedProviders, $key );
	}

	public function offsetGet( $key )
	{
		return $this->loadedProviders[$key];
	}

	public function offsetSet( $key, $value )
	{
		$this->register( $value, $key );
	}

	public function offsetUnset( $key )
	{
		$this->forget( $key );
	}
}
