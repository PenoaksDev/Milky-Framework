<?php namespace Milky\Http\Routing\Matching;

use Milky\Http\Request;
use Milky\Http\Routing\Route;

class SchemeValidator implements ValidatorInterface
{
	/**
	 * Validate a given rule against a route and request.
	 *
	 * @param  Route $route
	 * @param  Request $request
	 * @return bool
	 */
	public function matches( Route $route, Request $request )
	{
		if ( $route->httpOnly() )
			return !$request->secure();
		elseif ( $route->secure() )
			return $request->secure();

		return true;
	}
}
