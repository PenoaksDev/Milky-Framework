<?php

namespace Foundation\Bus;

use Foundation\Support\ServiceProvider;

class BusServiceProvider extends ServiceProvider
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
		$this->app->singleton('Foundation\Bus\Dispatcher', function ($app) {
			return new Dispatcher($app, function ($connection = null) use ($app) {
				return $app['Foundation\Contracts\Queue\Factory']->connection($connection);
			});
		});

		$this->app->alias(
			'Foundation\Bus\Dispatcher', 'Foundation\Contracts\Bus\Dispatcher'
		);

		$this->app->alias(
			'Foundation\Bus\Dispatcher', 'Foundation\Contracts\Bus\QueueingDispatcher'
		);
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [
			'Foundation\Bus\Dispatcher',
			'Foundation\Contracts\Bus\Dispatcher',
			'Foundation\Contracts\Bus\QueueingDispatcher',
		];
	}
}
