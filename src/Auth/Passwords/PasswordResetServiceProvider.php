<?php

namesapce Penoaks\Auth\Passwords;

use Foundation\Support\ServiceProvider;

class PasswordResetServiceProvider extends ServiceProvider
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
		$this->registerPasswordBroker();
	}

	/**
	 * Register the password broker instance.
	 *
	 * @return void
	 */
	protected function registerPasswordBroker()
	{
		$this->fw->bindings->singleton('auth.password', function ($fw)
{
			return new PasswordBrokerManager($fw);
		});

		$this->fw->bindings->bind('auth.password.broker', function ($fw)
{
			return $fw->make('auth.password')->broker();
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return ['auth.password', 'auth.password.broker'];
	}
}
