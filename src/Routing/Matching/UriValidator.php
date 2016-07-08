<?php

namespace Penoaks\Routing\Matching;

use Penoaks\Http\Request;
use Penoaks\Routing\Route;

class UriValidator implements ValidatorInterface
{
	/**
	 * Validate a given rule against a route and request.
	 *
	 * @param  \Penoaks\Routing\Route  $route
	 * @param  \Penoaks\Http\Request  $request
	 * @return bool
	 */
	public function matches(Route $route, Request $request)
	{
		$path = $request->path() == '/' ? '/' : '/'.$request->path();

		return preg_match($route->getCompiled()->getRegex(), rawurldecode($path));
	}
}
