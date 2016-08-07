<?php namespace Milky\Http\Routing\Matching;

use Milky\Http\Request;
use Milky\Http\Routing\Route;

interface ValidatorInterface
{
	/**
	 * Validate a given rule against a route and request.
	 *
	 * @param  Route $route
	 * @param  Request $request
	 * @return bool
	 */
	public function matches( Route $route, Request $request );
}
