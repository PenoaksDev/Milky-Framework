<?php
namespace Penoaks\Auth;

use Penoaks\Auth\Access\Gate;
use Penoaks\Barebones\ServiceProvider;
use Penoaks\Contracts\Auth\Access\Gate as GateContract;
use Penoaks\Contracts\Auth\Authenticatable as AuthenticatableContract;

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
		$this->bindings->singleton( 'auth', function ( $bindings )
		{
			// Once the authentication service has actually been requested by the developer
			// we will set a variable in the application indicating such. This helps us
			// know that we need to set any queued cookies in the after event later.
			$bindings->bindings['auth.loaded'] = true;

			return new AuthManager( $bindings );
		} );

		$this->bindings->singleton( 'auth.driver', function ( $bindings )
		{
			return $bindings['auth']->guard();
		} );
	}

	/**
	 * Register a resolver for the authenticated user.
	 *
	 * @return void
	 */
	protected function registerUserResolver()
	{
		$this->bindings->bind( AuthenticatableContract::class, function ( $bindings )
		{
			return call_user_func( $bindings['auth']->userResolver() );
		} );
	}

	/**
	 * Register the access gate service.
	 *
	 * @return void
	 */
	protected function registerAccessGate()
	{
		$this->bindings->singleton( GateContract::class, function ( $bindings )
		{
			return new Gate( $bindings, function () use ( $bindings )
			{
				return call_user_func( $bindings['auth']->userResolver() );
			} );
		} );
	}

	/**
	 * Register a resolver for the authenticated user.
	 *
	 * @return void
	 */
	protected function registerRequestRebindHandler()
	{
		$this->bindings->rebinding( 'request', function ( $bindings, $request )
		{
			$request->setUserResolver( function ( $guard = null ) use ( $bindings )
			{
				return call_user_func( $bindings['auth']->userResolver(), $guard );
			} );
		} );
	}
}
