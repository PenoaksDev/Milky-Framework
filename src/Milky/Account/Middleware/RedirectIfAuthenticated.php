<?php namespace Milky\Account\Middleware;

use Closure;
use Milky\Facades\Acct;
use Milky\Facades\Redirect;
use Milky\Http\Request;

class RedirectIfAuthenticated
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
		if ( Acct::check() )
			return Redirect::to( '/' );

		return $next( $request );
	}
}
