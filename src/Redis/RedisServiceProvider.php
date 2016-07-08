<?php

namespace Penoaks\Redis;

use Penoaks\Support\ServiceProvider;

class RedisServiceProvider extends ServiceProvider
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
		$this->fw->bindings->singleton('redis', function ($fw)
{
			return new Database($fw->bindings['config']['database.redis']);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return ['redis'];
	}
}
