<?php

namesapce Penoaks\Auth;

use Foundation\Http\Request;
use Foundation\Support\Facades\Auth;
use Foundation\Support\Facades\Lang;

trait AuthenticatesUsers
{
	use RedirectsUsers;

	/**
	 * Show the application login form.
	 *
	 * @return \Penoaks\Http\Response
	 */
	public function getLogin()
	{
		return $this->showLoginForm();
	}

	/**
	 * Show the application login form.
	 *
	 * @return \Penoaks\Http\Response
	 */
	public function showLoginForm()
	{
		$view = property_exists($this, 'loginView')
					? $this->loginView : 'auth.authenticate';

		if (view()->exists($view))
{
			return view($view);
		}

		return view('auth.login');
	}

	/**
	 * Handle a login request to the application.
	 *
	 * @param  \Penoaks\Http\Request  $request
	 * @return \Penoaks\Http\Response
	 */
	public function postLogin(Request $request)
	{
		return $this->login($request);
	}

	/**
	 * Handle a login request to the application.
	 *
	 * @param  \Penoaks\Http\Request  $request
	 * @return \Penoaks\Http\Response
	 */
	public function login(Request $request)
	{
		$this->validateLogin($request);

		// If the class is using the ThrottlesLogins trait, we can automatically throttle
		// the login attempts for this application. We'll key this by the username and
		// the IP address of the client making these requests into this application.
		$throttles = $this->isUsingThrottlesLoginsTrait();

		if ($throttles && $lockedOut = $this->hasTooManyLoginAttempts($request))
{
			$this->fireLockoutEvent($request);

			return $this->sendLockoutResponse($request);
		}

		$credentials = $this->getCredentials($request);

		if (Auth::guard($this->getGuard())->attempt($credentials, $request->has('remember')))
{
			return $this->handleUserWasAuthenticated($request, $throttles);
		}

		// If the login attempt was unsuccessful we will increment the number of attempts
		// to login and redirect the user back to the login form. Of course, when this
		// user surpasses their maximum number of attempts they will get locked out.
		if ($throttles && ! $lockedOut)
{
			$this->incrementLoginAttempts($request);
		}

		return $this->sendFailedLoginResponse($request);
	}

	/**
	 * Validate the user login request.
	 *
	 * @param  \Penoaks\Http\Request  $request
	 * @return void
	 */
	protected function validateLogin(Request $request)
	{
		$this->validate($request, [
			$this->loginUsername() => 'required', 'password' => 'required',
		]);
	}

	/**
	 * Send the response after the user was authenticated.
	 *
	 * @param  \Penoaks\Http\Request  $request
	 * @param  bool  $throttles
	 * @return \Penoaks\Http\Response
	 */
	protected function handleUserWasAuthenticated(Request $request, $throttles)
	{
		if ($throttles)
{
			$this->clearLoginAttempts($request);
		}

		if (method_exists($this, 'authenticated'))
{
			return $this->authenticated($request, Auth::guard($this->getGuard())->user());
		}

		return redirect()->intended($this->redirectPath());
	}

	/**
	 * Get the failed login response instance.
	 *
	 * @param \Penoaks\Http\Request  $request
	 * @return \Penoaks\Http\Response
	 */
	protected function sendFailedLoginResponse(Request $request)
	{
		return redirect()->back()
			->withInput($request->only($this->loginUsername(), 'remember'))
			->withErrors([
				$this->loginUsername() => $this->getFailedLoginMessage(),
			]);
	}

	/**
	 * Get the failed login message.
	 *
	 * @return string
	 */
	protected function getFailedLoginMessage()
	{
		return Lang::has('auth.failed')
				? Lang::get('auth.failed')
				: 'These credentials do not match our records.';
	}

	/**
	 * Get the needed authorization credentials from the request.
	 *
	 * @param  \Penoaks\Http\Request  $request
	 * @return array
	 */
	protected function getCredentials(Request $request)
	{
		return $request->only($this->loginUsername(), 'password');
	}

	/**
	 * Log the user out of the application.
	 *
	 * @return \Penoaks\Http\Response
	 */
	public function getLogout()
	{
		return $this->logout();
	}

	/**
	 * Log the user out of the application.
	 *
	 * @return \Penoaks\Http\Response
	 */
	public function logout()
	{
		Auth::guard($this->getGuard())->logout();

		return redirect(property_exists($this, 'redirectAfterLogout') ? $this->redirectAfterLogout : '/');
	}

	/**
	 * Get the guest middleware for the application.
	 */
	public function guestMiddleware()
	{
		$guard = $this->getGuard();

		return $guard ? 'guest:'.$guard : 'guest';
	}

	/**
	 * Get the login username to be used by the controller.
	 *
	 * @return string
	 */
	public function loginUsername()
	{
		return property_exists($this, 'username') ? $this->username : 'email';
	}

	/**
	 * Determine if the class is using the ThrottlesLogins trait.
	 *
	 * @return bool
	 */
	protected function isUsingThrottlesLoginsTrait()
	{
		return in_array(
			ThrottlesLogins::class, class_uses_recursive(static::class)
		);
	}

	/**
	 * Get the guard to be used during authentication.
	 *
	 * @return string|null
	 */
	protected function getGuard()
	{
		return property_exists($this, 'guard') ? $this->guard : null;
	}
}
