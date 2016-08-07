<?php namespace Milky\Auth\Middleware;

use Closure;
use Milky\Auth\AuthManager;
use Milky\Http\Request;

class AuthenticateWithBasicAuth
{
	/**
	 * The guard AuthManager instance.
	 *
	 * @var AuthManager
	 */
	protected $auth;

	/**
	 * Create a new middleware instance.
	 *
	 * @param  AuthManager $auth
	 * @return void
	 */
	public function __construct( AuthManager $auth )
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
