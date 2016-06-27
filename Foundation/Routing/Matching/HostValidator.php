<?php

namespace Foundation\Routing\Matching;

use Foundation\Http\Request;
use Foundation\Routing\Route;

class HostValidator implements ValidatorInterface
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
		if (is_null($route->getCompiled()->getHostRegex())) {
			return true;
		}

		return preg_match($route->getCompiled()->getHostRegex(), $request->getHost());
	}
}
