<?php namespace Milky\Auth\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as AuthFactory;

class AuthenticateWithBasicAuth
{
	/**
	 * The guard factory instance.
	 *
	 * @var Factory
	 */
	protected $auth;

	/**
	 * Create a new middleware instance.
	 *
	 * @param  Factory $auth
	 * @return void
	 */
	public function __construct( AuthFactory $auth )
	{
		$this->auth = $auth;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  Request $request
	 * @param  \Closure $next
	 * @param  string|null $guard
	 * @return mixed
	 */
	public function handle( $request, Closure $next, $guard = null )
	{
		return $this->auth->guard( $guard )->basic() ?: $next( $request );
	}
}
