<?php

namespace Penoaks\Cache;

use Penoaks\Cache\Console\ClearCommand;
use Penoaks\Barebones\ServiceProvider;

class CacheServiceProvider extends ServiceProvider
{
	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->bindings->singleton( 'cache', function ( $bindings )
		{
			return new CacheManager( $bindings );
		} );

		$this->bindings->singleton( 'cache.store', function ( $bindings )
		{
			return $bindings['cache']->driver();
		} );

		$this->bindings->singleton( 'memcached.connector', function ()
		{
			return new MemcachedConnector;
		} );

		$this->registerCommands();
	}

	/**
	 * Register the cache related console commands.
	 *
	 * @return void
	 */
	public function registerCommands()
	{
		$this->bindings->singleton( 'command.cache.clear', function ( $bindings )
		{
			return new ClearCommand( $bindings['cache'] );
		} );

		$this->commands( 'command.cache.clear' );
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [
			'cache',
			'cache.store',
			'memcached.connector',
			'command.cache.clear',
		];
	}
}
