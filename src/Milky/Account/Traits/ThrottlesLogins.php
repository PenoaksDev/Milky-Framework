<?php namespace Milky\Account\Traits;

use Milky\Binding\UniversalBuilder;
use Milky\Cache\RateLimiter;
use Milky\Facades\Hooks;
use Milky\Facades\Lang;
use Milky\Http\RedirectResponse;
use Milky\Http\Request;

/**
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
trait ThrottlesLogins
{
	/**
	 * Determine if the user has too many failed login attempts.
	 *
	 * @param  Request $request
	 * @return bool
	 */
	protected function hasTooManyLoginAttempts( Request $request )
	{
		return UniversalBuilder::resolveClass( RateLimiter::class )->tooManyAttempts( $this->getThrottleKey( $request ), $this->maxLoginAttempts(), $this->lockoutTime() / 60 );
	}

	/**
	 * Increment the login attempts for the user.
	 *
	 * @param  Request $request
	 * @return int
	 */
	protected function incrementLoginAttempts( Request $request )
	{
		UniversalBuilder::resolveClass( RateLimiter::class )->hit( $this->getThrottleKey( $request ) );
	}

	/**
	 * Determine how many retries are left for the user.
	 *
	 * @param  Request $request
	 * @return int
	 */
	protected function retriesLeft( Request $request )
	{
		return UniversalBuilder::resolveClass( RateLimiter::class )->retriesLeft( $this->getThrottleKey( $request ), $this->maxLoginAttempts() );
	}

	/**
	 * Redirect the user after determining they are locked out.
	 *
	 * @param  Request $request
	 * @return RedirectResponse
	 */
	protected function sendLockoutResponse( Request $request )
	{
		$seconds = $this->secondsRemainingOnLockout( $request );

		return redirect()->back()->withInput( $request->only( $this->loginUsername(), 'remember' ) )->withErrors( [
				$this->loginUsername() => $this->getLockoutErrorMessage( $seconds ),
			] );
	}

	/**
	 * Get the login lockout error message.
	 *
	 * @param  int $seconds
	 * @return string
	 */
	protected function getLockoutErrorMessage( $seconds )
	{
		return Lang::has( 'auth.throttle' ) ? Lang::get( 'auth.throttle', ['seconds' => $seconds] ) : 'Too many login attempts. Please try again in ' . $seconds . ' seconds.';
	}

	/**
	 * Get the lockout seconds.
	 *
	 * @param  Request $request
	 * @return int
	 */
	protected function secondsRemainingOnLockout( Request $request )
	{
		return UniversalBuilder::resolveClass( RateLimiter::class )->availableIn( $this->getThrottleKey( $request ) );
	}

	/**
	 * Clear the login locks for the given user credentials.
	 *
	 * @param  Request $request
	 * @return void
	 */
	protected function clearLoginAttempts( Request $request )
	{
		UniversalBuilder::resolveClass( RateLimiter::class )->clear( $this->getThrottleKey( $request ) );
	}

	/**
	 * Get the throttle key for the given request.
	 *
	 * @param  Request $request
	 * @return string
	 */
	protected function getThrottleKey( Request $request )
	{
		return mb_strtolower( $request->input( $this->loginUsername() ) ) . '|' . $request->ip();
	}

	/**
	 * Get the maximum number of login attempts for delaying further attempts.
	 *
	 * @return int
	 */
	protected function maxLoginAttempts()
	{
		return property_exists( $this, 'maxLoginAttempts' ) ? $this->maxLoginAttempts : 5;
	}

	/**
	 * The number of seconds to delay further login attempts.
	 *
	 * @return int
	 */
	protected function lockoutTime()
	{
		return property_exists( $this, 'lockoutTime' ) ? $this->lockoutTime : 60;
	}

	/**
	 * Fire an event when a lockout occurs.
	 *
	 * @param  Request $request
	 * @return void
	 */
	protected function fireLockoutEvent( Request $request )
	{
		Hooks::trigger( 'user.lockout', compact( 'request' ) );
	}
}
