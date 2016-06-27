<?php

namespace Foundation\Routing\Matching;

use Foundation\Http\Request;
use Foundation\Routing\Route;

class MethodValidator implements ValidatorInterface
{
	/**
	 * Validate a given rule against a route and request.
	 *
	 * @param  \Foundation\Routing\Route  $route
	 * @param  \Foundation\Http\Request  $request
	 * @return bool
	 */
	public function matches(Route $route, Request $request)
	{
		return in_array($request->getMethod(), $route->methods());
	}
}
