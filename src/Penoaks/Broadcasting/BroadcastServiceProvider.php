<?php

namespace Penoaks\Broadcasting;

use Penoaks\Barebones\ServiceProvider;

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
		$this->bindings->singleton( 'Penoaks\Broadcasting\BroadcastManager', function ( $bindings )
		{
			return new BroadcastManager( $bindings );
		} );

		$this->bindings->singleton( 'Penoaks\Contracts\Broadcasting\Broadcaster', function ( $bindings )
		{
			return $bindings->make( 'Penoaks\Broadcasting\BroadcastManager' )->connection();
		} );

		$this->bindings->alias( 'Penoaks\Broadcasting\BroadcastManager', 'Penoaks\Contracts\Broadcasting\Factory' );
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
