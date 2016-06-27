<?php

namespace Foundation\Broadcasting;

use Foundation\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
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
		$this->app->singleton('Foundation\Broadcasting\BroadcastManager', function ($app) {
			return new BroadcastManager($app);
		});

		$this->app->singleton('Foundation\Contracts\Broadcasting\Broadcaster', function ($app) {
			return $app->make('Foundation\Broadcasting\BroadcastManager')->connection();
		});

		$this->app->alias(
			'Foundation\Broadcasting\BroadcastManager', 'Foundation\Contracts\Broadcasting\Factory'
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
			'Foundation\Broadcasting\BroadcastManager',
			'Foundation\Contracts\Broadcasting\Factory',
			'Foundation\Contracts\Broadcasting\Broadcaster',
		];
	}
}
