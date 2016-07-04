<?php

namespace Foundation\Auth\Middleware;

use Closure;
use Foundation\Contracts\Auth\Factory as AuthFactory;

class AuthenticateWithBasicAuth
{
	/**
	 * The guard factory instance.
	 *
	 * @var \Foundation\Contracts\Auth\Factory
	 */
	protected $auth;

	/**
	 * Create a new middleware instance.
	 *
	 * @param  \Foundation\Contracts\Auth\Factory  $auth
	 * @return void
	 */
	public function __construct(AuthFactory $auth)
	{
		$this->auth = $auth;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Foundation\Http\Request  $request
	 * @param  \Closure  $next
	 * @param  string|null  $guard
	 * @return mixed
	 */
	public function handle($request, Closure $next, $guard = null)
	{
		return $this->auth->guard($guard)->basic() ?: $next($request);
	}
}
