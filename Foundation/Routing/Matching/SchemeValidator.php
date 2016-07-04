<?php

namespace Foundation\Routing\Matching;

use Foundation\Http\Request;
use Foundation\Routing\Route;

class SchemeValidator implements ValidatorInterface
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
		if ($route->httpOnly())
{
			return ! $request->secure();
		}
elseif ($route->secure())
{
			return $request->secure();
		}

		return true;
	}
}
