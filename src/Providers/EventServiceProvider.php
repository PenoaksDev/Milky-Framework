<?php
namespace Penoaks\Providers;

use Penoaks\Events\Dispatcher;
use Penoaks\Barebones\ServiceProvider;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
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
	 * @param  Dispatcher $events
	 * @return void
	 */
	public function boot( Dispatcher $events )
	{
		foreach ( $this->listens() as $event => $listeners )
		{
			foreach ( $listeners as $listener )
			{
				$events->listen( $event, $listener );
			}
		}

		foreach ( $this->subscribe as $subscriber )
		{
			$events->subscribe( $subscriber );
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
