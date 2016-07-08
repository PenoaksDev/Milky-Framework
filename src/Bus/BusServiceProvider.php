<?php

namespace Penoaks\Bus;

use Penoaks\Support\ServiceProvider;

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
		$this->fw->bindings->singleton('Penoaks\Bus\Dispatcher', function ($fw)
{
			return new Dispatcher($fw, function ($connection = null) use ($fw)
{
				return $fw->bindings['Penoaks\Contracts\Queue\Factory']->connection($connection);
			});
		});

		$this->fw->bindings->alias(
			'Penoaks\Bus\Dispatcher', 'Penoaks\Contracts\Bus\Dispatcher'
		);

		$this->fw->bindings->alias(
			'Penoaks\Bus\Dispatcher', 'Penoaks\Contracts\Bus\QueueingDispatcher'
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
			'Penoaks\Bus\Dispatcher',
			'Penoaks\Contracts\Bus\Dispatcher',
			'Penoaks\Contracts\Bus\QueueingDispatcher',
		];
	}
}
