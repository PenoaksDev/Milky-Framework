<?php

namespace Foundation\Auth;

use Foundation\Auth\Access\Gate;
use Foundation\Support\ServiceProvider;
use Foundation\Contracts\Auth\Access\Gate as GateContract;
use Foundation\Contracts\Auth\Authenticatable as AuthenticatableContract;

class AuthServiceProvider extends ServiceProvider
{
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerAuthenticator();

		$this->registerUserResolver();

		$this->registerAccessGate();

		$this->registerRequestRebindHandler();
	}

	/**
	 * Register the authenticator services.
	 *
	 * @return void
	 */
	protected function registerAuthenticator()
	{
		$this->fw->bindings->singleton('auth', function ($fw)
{
			// Once the authentication service has actually been requested by the developer
			// we will set a variable in the application indicating such. This helps us
			// know that we need to set any queued cookies in the after event later.
			$fw->bindings['auth.loaded'] = true;

			return new AuthManager($fw);
		});

		$this->fw->bindings->singleton('auth.driver', function ($fw)
{
			return $fw->bindings['auth']->guard();
		});
	}

	/**
	 * Register a resolver for the authenticated user.
	 *
	 * @return void
	 */
	protected function registerUserResolver()
	{
		$this->fw->bindings->bind(
			AuthenticatableContract::class, function ($fw)
{
				return call_user_func($fw->bindings['auth']->userResolver());
			}
		);
	}

	/**
	 * Register the access gate service.
	 *
	 * @return void
	 */
	protected function registerAccessGate()
	{
		$this->fw->bindings->singleton(GateContract::class, function ($fw)
{
			return new Gate($fw, function () use ($fw)
{
				return call_user_func($fw->bindings['auth']->userResolver());
			});
		});
	}

	/**
	 * Register a resolver for the authenticated user.
	 *
	 * @return void
	 */
	protected function registerRequestRebindHandler()
	{
		$this->fw->rebinding('request', function ($fw, $request)
{
			$request->setUserResolver(function ($guard = null) use ($fw)
{
				return call_user_func($fw->bindings['auth']->userResolver(), $guard);
			});
		});
	}
}
