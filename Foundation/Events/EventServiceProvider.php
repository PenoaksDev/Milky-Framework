<?php
namespace Foundation\Events;

use Foundation\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->fw->bindings->singleton('events', function ($bindings)
{
			return (new Dispatcher($bindings))->setQueueResolver(function () use ($bindings)
{
				return $bindings->make('Foundation\Contracts\Queue\Factory');
			});
		});
	}
}
