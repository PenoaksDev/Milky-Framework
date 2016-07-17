<?php

namespace Penoaks\Bus;

use Penoaks\Barebones\ServiceProvider;

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
		$this->bindings->singleton( 'Penoaks\Bus\Dispatcher', function ( $bindings )
		{
			return new Dispatcher( $bindings, function ( $connection = null ) use ( $bindings )
			{
				return $bindings['Penoaks\Contracts\Queue\Factory']->connection( $connection );
			} );
		} );

		$this->bindings->alias( 'Penoaks\Bus\Dispatcher', 'Penoaks\Contracts\Bus\Dispatcher' );

		$this->bindings->alias( 'Penoaks\Bus\Dispatcher', 'Penoaks\Contracts\Bus\QueueingDispatcher' );
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [
			'Penoaks\Bus\Dispatcher',
			'Penoaks\Contracts\Bus\Dispatcher',
			'Penoaks\Contracts\Bus\QueueingDispatcher',
		];
	}
}
