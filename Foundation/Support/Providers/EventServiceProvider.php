<?php

namespace Foundation\Support\Providers;

use Foundation\Support\ServiceProvider;
use Foundation\Contracts\Events\Dispatcher as DispatcherContract;

class EventServiceProvider extends ServiceProvider
{
	/**
	 * The event handler mappings for the application.
	 *
	 * @var array
	 */
	protected $listen = [];

	/**
	 * The subscriber classes to register.
	 *
	 * @var array
	 */
	protected $subscribe = [];

	/**
	 * Register the application's event listeners.
	 *
	 * @param  \Foundation\Contracts\Events\Dispatcher  $events
	 * @return void
	 */
	public function boot(DispatcherContract $events)
	{
		foreach ($this->listens() as $event => $listeners) {
			foreach ($listeners as $listener) {
				$events->listen($event, $listener);
			}
		}

		foreach ($this->subscribe as $subscriber) {
			$events->subscribe($subscriber);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		//
	}

	/**
	 * Get the events and handlers.
	 *
	 * @return array
	 */
	public function listens()
	{
		return $this->listen;
	}
}
