<?php namespace Milky\Account\Permissions;

use Closure;
use Milky\Facades\Acct;
use Milky\Facades\Redirect;
use Milky\Facades\View;
use Milky\Http\Request;

class CheckPermission
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  Request $request
	 * @param  \Closure $next
	 * @return mixed
	 */
	public function handle( $request, Closure $next, $perm = null )
	{
		if ( $perm == null || empty( $perm ) )
			return $next( $request );

		if ( Acct::check() )
		{
			if ( PermissionManager::checkPermission( $perm ) )
				return $next( $request );
			else
			{
				// TODO Redirect to login with error message?
				return View::render( "permissions.denied" );
			}
		}

		return Redirect::to( 'acct/login' );
	}
}
