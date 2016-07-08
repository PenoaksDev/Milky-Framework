<?php

namespace Penoaks\Cache;

use Penoaks\Support\ServiceProvider;
use Penoaks\Cache\Console\ClearCommand;

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
		$this->fw->bindings->singleton('cache', function ($fw)
{
			return new CacheManager($fw);
		});

		$this->fw->bindings->singleton('cache.store', function ($fw)
{
			return $fw->bindings['cache']->driver();
		});

		$this->fw->bindings->singleton('memcached.connector', function ()
{
			return new MemcachedConnector;
		});

		$this->registerCommands();
	}

	/**
	 * Register the cache related console commands.
	 *
	 * @return void
	 */
	public function registerCommands()
	{
		$this->fw->bindings->singleton('command.cache.clear', function ($fw)
{
			return new ClearCommand($fw->bindings['cache']);
		});

		$this->commands('command.cache.clear');
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [
			'cache', 'cache.store', 'memcached.connector', 'command.cache.clear',
		];
	}
}
