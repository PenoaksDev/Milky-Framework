<?php namespace Milky\Providers;

use Milky\Framework;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
abstract class ServiceProvider
{
	/**
	 * The paths that should be published.
	 *
	 * @var array
	 */
	protected static $publishes = [];

	/**
	 * The paths that should be published by group.
	 *
	 * @var array
	 */
	protected static $publishGroups = [];

	/**
	 * Merge the given configuration with the existing configuration.
	 *
	 * @param  string $path
	 * @param  string $key
	 */
	protected function mergeConfigFrom( $path, $key )
	{
		$config = Framework::fw()->config->get( $key, [] );
		Framework::fw()->set( $key, array_merge( require $path, $config ) );
	}

	/**
	 * Register a view file namespace.
	 *
	 * @param  string $path
	 * @param  string $namespace
	 */
	protected function loadViewsFrom( $path, $namespace )
	{
		if ( is_dir( $appPath = Framework::fw()->basePath . '/resources/views/vendor/' . $namespace ) )
			Framework::get( 'view' )->addNamespace( $namespace, $appPath );

		Framework::get( 'view' )->addNamespace( $namespace, $path );
	}

	/**
	 * Register a translation file namespace.
	 *
	 * @param  string $path
	 * @param  string $namespace
	 */
	protected function loadTranslationsFrom( $path, $namespace )
	{
		Framework::get( 'translator' )->addNamespace( $namespace, $path );
	}

	/**
	 * Register paths to be published by the publish command.
	 *
	 * @param  array $paths
	 * @param  string $group
	 */
	protected function publishes( array $paths, $group = null )
	{
		$class = static::class;

		if ( !array_key_exists( $class, static::$publishes ) )
		{
			static::$publishes[$class] = [];
		}

		static::$publishes[$class] = array_merge( static::$publishes[$class], $paths );

		if ( $group )
		{
			if ( !array_key_exists( $group, static::$publishGroups ) )
			{
				static::$publishGroups[$group] = [];
			}

			static::$publishGroups[$group] = array_merge( static::$publishGroups[$group], $paths );
		}
	}

	/**
	 * Get the paths to publish.
	 *
	 * @param  string $provider
	 * @param  string $group
	 * @return array
	 */
	public static function pathsToPublish( $provider = null, $group = null )
	{
		if ( $provider && $group )
		{
			if ( empty( static::$publishes[$provider] ) || empty( static::$publishGroups[$group] ) )
			{
				return [];
			}

			return array_intersect_key( static::$publishes[$provider], static::$publishGroups[$group] );
		}

		if ( $group && array_key_exists( $group, static::$publishGroups ) )
		{
			return static::$publishGroups[$group];
		}

		if ( $provider && array_key_exists( $provider, static::$publishes ) )
		{
			return static::$publishes[$provider];
		}

		if ( $group || $provider )
		{
			return [];
		}

		$paths = [];

		foreach ( static::$publishes as $class => $publish )
		{
			$paths = array_merge( $paths, $publish );
		}

		return $paths;
	}

	/**
	 * Register the package's custom Artisan commands.
	 *
	 * @param  array|mixed $commands
	 */
	public function commands( $commands )
	{
		$commands = is_array( $commands ) ? $commands : func_get_args();

		// To register the commands with Artisan, we will grab each of the arguments
		// passed into the method and listen for Artisan "start" event which will
		// give us the Artisan console instance which we will give commands to.
		$events = $this->app['events'];

		$events->listen( ArtisanStarting::class, function ( $event ) use ( $commands )
		{
			$event->artisan->resolveCommands( $commands );
		} );
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [];
	}

	/**
	 * Get the events that trigger this service provider to register.
	 *
	 * @return array
	 */
	public function when()
	{
		return [];
	}

	/**
	 * Get a list of files that should be compiled for the package.
	 *
	 * @return array
	 */
	public static function compiles()
	{
		return [];
	}
}
