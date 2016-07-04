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
		$this->fw->bindings->singleton('Foundation\Broadcasting\BroadcastManager', function ($fw)
{
			return new BroadcastManager($fw);
		});

		$this->fw->bindings->singleton('Foundation\Contracts\Broadcasting\Broadcaster', function ($fw)
{
			return $fw->make('Foundation\Broadcasting\BroadcastManager')->connection();
		});

		$this->fw->bindings->alias(
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
