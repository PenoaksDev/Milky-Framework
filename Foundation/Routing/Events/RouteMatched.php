<?php

namespace Foundation\Routing\Events;

class RouteMatched
{
	/**
	 * The route instance.
	 *
	 * @var \Foundation\Routing\Route
	 */
	public $route;

	/**
	 * The request instance.
	 *
	 * @var \Foundation\Http\Request
	 */
	public $request;

	/**
	 * Create a new event instance.
	 *
	 * @param  \Foundation\Routing\Route  $route
	 * @param  \Foundation\Http\Request  $request
	 * @return void
	 */
	public function __construct($route, $request)
	{
		$this->route = $route;
		$this->request = $request;
	}
}
