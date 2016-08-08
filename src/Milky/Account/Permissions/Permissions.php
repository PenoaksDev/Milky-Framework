<?php namespace Milky\Account\Permissions;

use Closure;
use HolyWorlds\Support\Util;
use Milky\Account\Models\PermissionDefaults;
use Milky\Facades\Acct;
use Milky\Facades\Redirect;
use Milky\Facades\View;
use Milky\Http\Request;

class Permissions
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
		{
			return $next( $request );
		}

		if ( Acct::check() )
		{
			if ( self::checkPermission( $perm ) )
				return $next( $request );
			else
				// TODO Redirect to login with error message?
				return View::render( "permissions.denied" );
		}

		return Redirect::to( 'acct/login' );
	}

	public static function checkPermission( $permission, $entity = null )
	{
		if ( empty( $permission ) )
			return true;

		if ( $entity === null )
		{
			if ( !Acct::check() )
				return false;
			$entity = Acct::acct();
		}

		$def = PermissionDefaults::find( $permission );

		foreach ( $entity->permissions as $p )
		{
			if ( empty( $p->permission ) )
			{
				continue;
			} // Ignore empty permissions

			try
			{
				if ( preg_match( Util::prepareExpression( $p->permission ), $permission ) )
				{
					return empty( $p->value ) ? ( $def === null ? true : $def->value_assigned ) : $p->value;
				}
			}
			catch ( \Exception $e )
			{
				// Ignore preg_match() exceptions
			}
		}

		foreach ( $entity->groups() as $group )
		{
			$result = self::checkPermission( $permission, $group ); // TODO Compare group results and sort by weight
			if ( $result !== false )
				return $result;
		}

		return $def === null ? false : $def->value_default;
	}
}
