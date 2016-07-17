<?php
namespace Penoaks\Session;

use Penoaks\Barebones\ServiceProvider;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class SessionServiceProvider extends ServiceProvider
{
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerSessionManager();

		$this->registerSessionDriver();

		$this->bindings->singleton( 'Penoaks\Session\Middleware\StartSession' );
	}

	/**
	 * Register the session manager instance.
	 *
	 * @return void
	 */
	protected function registerSessionManager()
	{
		$this->bindings->singleton( 'session', function ( $bindings )
		{
			return new SessionManager( $bindings );
		} );
	}

	/**
	 * Register the session driver instance.
	 *
	 * @return void
	 */
	protected function registerSessionDriver()
	{
		$this->bindings->singleton( 'session.store', function ( $bindings )
		{
			// First, we will create the session manager which is responsible for the
			// creation of the various session drivers when they are needed by the
			// application instance, and will resolve them on a lazy load basis.
			$manager = $bindings['session'];

			return $manager->driver();
		} );
	}
}
