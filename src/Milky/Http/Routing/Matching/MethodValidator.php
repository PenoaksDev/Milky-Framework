<?php namespace Milky\Http\Routing\Matching;

use Milky\Http\Request;
use Milky\Http\Routing\Route;

class MethodValidator implements ValidatorInterface
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
		return in_array( $request->getMethod(), $route->methods() );
	}
}
