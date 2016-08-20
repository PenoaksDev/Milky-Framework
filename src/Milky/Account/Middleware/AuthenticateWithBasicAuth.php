<?php namespace Milky\Account\Middleware;

use Closure;
use Milky\Account\AccountManager;
use Milky\Auth\AuthManager;
use Milky\Http\Request;

class AuthenticateWithBasicAuth
{
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
		return AccountManager::i()->guard( $guard )->basic() ?: $next( $request );
	}
}
