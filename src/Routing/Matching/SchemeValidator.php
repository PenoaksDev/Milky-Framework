<?php

namespace Penoaks\Routing\Matching;

use Penoaks\Http\Request;
use Penoaks\Routing\Route;

class SchemeValidator implements ValidatorInterface
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
