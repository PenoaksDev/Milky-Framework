<?php namespace Milky\Account\Traits;

use Milky\Facades\Acct;
use Milky\Facades\Lang;
use Milky\Facades\Redirect;
use Milky\Facades\View;
use Milky\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
trait AuthenticatesUsers
{
	use RedirectsUsers;

	/**
	 * Determines the guard involved
	 *
	 * @var string
	 */
	private $guardAuth = 'web';

	/**
	 * Show the application login form.
	 *
	 * @return Response
	 */
	public function getLogin()
	{
		return $this->showLoginForm();
	}

	/**
	 * Show the application login form.
	 *
	 * @return Response
	 */
	public function showLoginForm()
	{
		$view = property_exists( $this, 'loginView' ) ? $this->loginView : 'auth.authenticate';
		if ( View::exists( $view ) )
			return View::render( $view );

		return View::render( 'auth.login' );
	}

	/**
	 * Handle a login request to the application.
	 *
	 * @param  Request $request
	 * @return Response
	 */
	public function postLogin( Request $request )
	{
		return $this->login( $request );
	}

	/**
	 * Handle a login request to the application.
	 *
	 * @param  Request $request
	 * @return Response
	 */
	public function login( Request $request )
	{
		$this->validateLogin( $request );

		// If the class is using the ThrottlesLogins trait, we can automatically throttle
		// the login attempts for this application. We'll key this by the username and
		// the IP address of the client making these requests into this application.
		$throttles = $this->isUsingThrottlesLoginsTrait();

		if ( $throttles && $lockedOut = $this->hasTooManyLoginAttempts( $request ) )
		{
			$this->fireLockoutEvent( $request );

			return $this->sendLockoutResponse( $request );
		}

		$credentials = $this->getCredentials( $request );
		if ( Acct::guard( $this->getGuard() )->attempt( $credentials, $request->has( 'remember' ) ) )
			return $this->handleUserWasAuthenticated( $request, $throttles );

		// If the login attempt was unsuccessful we will increment the number of attempts
		// to login and redirect the user back to the login form. Of course, when this
		// user surpasses their maximum number of attempts they will get locked out.
		if ( $throttles && !$lockedOut )
			$this->incrementLoginAttempts( $request );

		return $this->sendFailedLoginResponse( $request );
	}

	/**
	 * Validate the user login request.
	 *
	 * @param  Request $request
	 * @return void
	 */
	protected function validateLogin( Request $request )
	{
		$this->validate( $request, [
			$this->loginUsername() => 'required',
			'password' => 'required',
		] );
	}

	/**
	 * Send the response after the user was authenticated.
	 *
	 * @param  Request $request
	 * @param  bool $throttles
	 * @return Response
	 */
	protected function handleUserWasAuthenticated( Request $request, $throttles )
	{
		if ( $throttles )
			$this->clearLoginAttempts( $request );

		if ( method_exists( $this, 'authenticated' ) )
			return $this->authenticated( $request, Acct::guard( $this->getGuard() )->acct() );

		return Redirect::intended( $this->redirectPath() );
	}

	/**
	 * Get the failed login response instance.
	 *
	 * @param Request $request
	 * @return Response
	 */
	protected function sendFailedLoginResponse( Request $request )
	{
		return Redirect::back()->withInput( $request->only( $this->loginUsername(), 'remember' ) )->withErrors( [
				$this->loginUsername() => $this->getFailedLoginMessage(),
			] );
	}

	/**
	 * Get the failed login message.
	 *
	 * @return string
	 */
	protected function getFailedLoginMessage()
	{
		return Lang::has( 'auth.failed' ) ? Lang::get( 'auth.failed' ) : 'These credentials do not match our records.';
	}

	/**
	 * Get the needed authorization credentials from the request.
	 *
	 * @param  Request $request
	 * @return array
	 */
	protected function getCredentials( Request $request )
	{
		return $request->only( $this->loginUsername(), 'password' );
	}

	/**
	 * Log the user out of the application.
	 *
	 * @return Response
	 */
	public function getLogout()
	{
		return $this->logout();
	}

	/**
	 * Log the user out of the application.
	 *
	 * @return Response
	 */
	public function logout()
	{
		Acct::guard( $this->getGuard() )->logout();

		return Redirect::to( property_exists( $this, 'redirectAfterLogout' ) ? $this->redirectAfterLogout : '/' )->withMessages( [ 'success' => "You are now logged out." ] );
	}

	/**
	 * Get the login username to be used by the controller.
	 *
	 * @return string
	 */
	public function loginUsername()
	{
		return property_exists( $this, 'username' ) ? $this->username : 'email';
	}

	/**
	 * Determine if the class is using the ThrottlesLogins trait.
	 *
	 * @return bool
	 */
	protected function isUsingThrottlesLoginsTrait()
	{
		return in_array( ThrottlesLogins::class, class_uses_recursive( static::class ) );
	}

	public function useGuard( $guard )
	{
		$this->guardAuth = $guard;
	}

	/**
	 * @return string
	 */
	public function getGuard()
	{
		return $this->guardAuth;
	}
}
