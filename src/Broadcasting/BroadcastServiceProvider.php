<?php

namespace Penoaks\Broadcasting;

use Penoaks\Support\ServiceProvider;

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
		$this->fw->bindings->singleton('Penoaks\Broadcasting\BroadcastManager', function ($fw)
{
			return new BroadcastManager($fw);
		});

		$this->fw->bindings->singleton('Penoaks\Contracts\Broadcasting\Broadcaster', function ($fw)
{
			return $fw->make('Penoaks\Broadcasting\BroadcastManager')->connection();
		});

		$this->fw->bindings->alias(
			'Penoaks\Broadcasting\BroadcastManager', 'Penoaks\Contracts\Broadcasting\Factory'
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
			'Penoaks\Broadcasting\BroadcastManager',
			'Penoaks\Contracts\Broadcasting\Factory',
			'Penoaks\Contracts\Broadcasting\Broadcaster',
		];
	}
}
