<?php
namespace Penoaks\Framework;

use Penoaks\Barebones\BaseArray;
use Penoaks\Barebones\ServiceProvider;
use Penoaks\Bindings\Bindings;
use Penoaks\Events\Runlevel;
use Penoaks\Events\ServiceProviderAddEvent;
use Penoaks\Facades\Bindings as B;
use Penoaks\Facades\Events;
use Penoaks\Framework;
use Penoaks\Framework as ApplicationContract;
use Psy\Exception\RuntimeException;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class ProviderRepository extends BaseArray
{
	/**
	 * Was the providers already booted?
	 *
	 * @var bool
	 */
	private $isBooted = false;

	/**
	 * Register the application service providers.
	 *
	 * @param  array $providers
	 * @return void
	 */
	public function load( array $providers )
	{
		$manifest = $this->loadManifest();

		// First we will load the service manifest, which contains information on all
		// service providers registered with the application and which services it
		// provides. This is used to know which services are "deferred" loaders.
		if ( $this->shouldRecompile( $manifest, $providers ) )
		{
			$manifest = $this->compileManifest( $providers );
		}

		// Next, we will register events to load the providers for each of the events
		// that it has requested. This allows the service provider to defer itself
		// while still getting automatically loaded when a certain event occurs.
		foreach ( $manifest['when'] as $provider => $events )
		{
			$this->registerLoadEvents( $provider, $events );
		}

		// We will go ahead and register all of the eagerly loaded providers with the
		// application so their services can be registered with the application as
		// a provided service. Then we will set the deferred service list on it.
		foreach ( $manifest['eager'] as $provider )
		{
			$this->fw->provider( $this->createProvider( $provider ) );
		}

		$this->fw->addDeferredServices( $manifest['deferred'] );
	}

	/**
	 * Register the load events for the given provider.
	 *
	 * @param  string $provider
	 * @param  array $events
	 * @return void
	 */
	protected function registerLoadEvents( $provider, array $events )
	{
		if ( count( $events ) < 1 )
			return;

		$fw = $this->fw;

		$fw->bindings->make( 'events' )->listen( $events, function () use ( $fw, $provider )
		{
			$fw->provider( $provider );
		} );
	}

	/**
	 * Compile the application manifest file.
	 *
	 * @param  array $providers
	 * @return array
	 */
	protected function compileManifest( $providers )
	{
		// The service manifest should contain a list of all of the providers for
		// the application so we can compare it on each request to the service
		// and determine if the manifest should be recompiled or is current.
		$manifest = $this->freshManifest( $providers );

		foreach ( $providers as $provider )
		{
			$instance = $this->createProvider( $provider );

			// When recompiling the service manifest, we will spin through each of the
			// providers and check if it's a deferred provider or not. If so we'll
			// add it's provided services to the manifest and note the provider.
			if ( $instance->isDeferred() )
			{
				foreach ( $instance->provides() as $service )
				{
					$manifest['deferred'][$service] = $provider;
				}

				$manifest['when'][$provider] = $instance->when();
			}

			// If the service providers are not deferred, we will simply add it to an
			// array of eagerly loaded providers that will get registered on every
			// request to this application instead of "lazy" loading every time.
			else
			{
				$manifest['eager'][] = $provider;
			}
		}

		return $this->writeManifest( $manifest );
	}

	/**
	 * Create a new provider instance.
	 *
	 * @param  string $provider
	 * @return ServiceProvider
	 */
	public function createProvider( $provider )
	{
		return new $provider( $this->fw );
	}

	/**
	 * Determine if the manifest should be compiled.
	 *
	 * @param  array $manifest
	 * @param  array $providers
	 * @return bool
	 */
	public function shouldRecompile( $manifest, $providers )
	{
		return is_null( $manifest ) || $manifest['providers'] != $providers;
	}

	/**
	 * Load the service provider manifest JSON file.
	 *
	 * @return array|null
	 */
	public function loadManifest()
	{
		// The service manifest is a file containing a JSON representation of every
		// service provided by the application and whether its provider is using
		// deferred loading or should be eagerly loaded on each request to us.
		if ( $this->files->exists( $this->manifestPath ) )
		{
			$manifest = $this->files->getRequire( $this->manifestPath );

			if ( $manifest )
			{
				return array_merge( ['when' => []], $manifest );
			}
		}

		return null;
	}

	/**
	 * Write the service manifest file to disk.
	 *
	 * @param  array $manifest
	 * @return array
	 */
	public function writeManifest( $manifest )
	{
		$this->files->put( $this->manifestPath, '<?php return ' . var_export( $manifest, true ) . ';' );

		return array_merge( ['when' => []], $manifest );
	}

	/**
	 * Create a fresh service manifest data structure.
	 *
	 * @param  array $providers
	 * @return array
	 */
	protected function freshManifest( array $providers )
	{
		return ['providers' => $providers, 'eager' => [], 'deferred' => []];
	}

	/**
	 * Internal method for calling events
	 *
	 * @param $method
	 * @param $key
	 * @param null $value
	 */
	protected function onCall( $method, $key, &$value = null )
	{
		if ( $method == 'addAll' )
			$values = &$value;
		else if ( $method == 'add' )
			$values = [$key => &$value];
		else
			return false;

		array_walk( $values, function ( &$value, &$key )
		{
			if ( is_string( $value ) )
			{
				$key = $value;
				$value = Bindings::i()->make( $value );
			}
			else if ( $value instanceof ServiceProvider )
				$key = get_class( $value );
			else
				throw new RuntimeException( "Provides must be a string or an instance of " . ServiceProvider::class );

			array_walk( $this->arr, function ( $value, $arrKey ) use ( $key )
			{
				if ( $arrKey instanceof $key )
					throw new RuntimeException( "Service Provider " . $key . " is already registered." );
			} );

			/* Make instance of service provider available in the bindings, if it provides an alias key */
			if ( property_exists( $value, 'alias' ) )
				Bindings::i()->instance( $value->alias, $value );

			if ( method_exists( $value, 'register' ) )
				Bindings::i()->call( [$value, 'register'] );

			// If the application has already booted, we will call this boot method on
			// the provider class so it has an opportunity to do its boot logic and
			// will be ready for any usage by the developer's application logic.
			if ( $this->isBooted )
				if ( method_exists( $value, 'boot' ) )
					Bindings::i()->call( [$value, 'boot'] );

			Events::fire( new ServiceProviderAddEvent( $value ) );

			Events::listenEvents( $value );

			$value = new ProviderWrapper( $value );
			$value->loaded = true;

			$this->arr[$key] = $value;
		} );

		return true; // Returning true cancels the add
	}

	public function bootProviders()
	{
		if ( !Framework::i()->isRunlevel( Runlevel::BOOT ) )
			return;

		$this->isBooted = true;

		array_walk( $this->arr, function ( $value, $key )
		{
			if ( !is_object( $value ) )
				throw new RuntimeException( "There was a problem, the provider is not an object." );
			if ( method_exists( $value, 'boot' ) )
				B::call( [$value, 'boot'] );
		} );
	}
}
