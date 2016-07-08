<?php

namesapce Penoaks\Routing\Matching;

use Foundation\Http\Request;
use Foundation\Routing\Route;

interface ValidatorInterface
{
	/**
	 * Validate a given rule against a route and request.
	 *
	 * @param  \Penoaks\Routing\Route  $route
	 * @param  \Penoaks\Http\Request  $request
	 * @return bool
	 */
	public function matches(Route $route, Request $request);
}
