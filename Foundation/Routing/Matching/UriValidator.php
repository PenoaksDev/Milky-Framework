<?php

namespace Foundation\Routing\Matching;

use Foundation\Http\Request;
use Foundation\Routing\Route;

class UriValidator implements ValidatorInterface
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
		$path = $request->path() == '/' ? '/' : '/'.$request->path();

		return preg_match($route->getCompiled()->getRegex(), rawurldecode($path));
	}
}
