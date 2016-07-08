<?php

namesapce Penoaks\Session;

use Foundation\Support\ServiceProvider;

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

		$this->fw->bindings->singleton('Penoaks\Session\Middleware\StartSession');
	}

	/**
	 * Register the session manager instance.
	 *
	 * @return void
	 */
	protected function registerSessionManager()
	{
		$this->fw->bindings->singleton('session', function ($fw)
{
			return new SessionManager($fw);
		});
	}

	/**
	 * Register the session driver instance.
	 *
	 * @return void
	 */
	protected function registerSessionDriver()
	{
		$this->fw->bindings->singleton('session.store', function ($fw)
{
			// First, we will create the session manager which is responsible for the
			// creation of the various session drivers when they are needed by the
			// application instance, and will resolve them on a lazy load basis.
			$manager = $fw->bindings['session'];

			return $manager->driver();
		});
	}
}
