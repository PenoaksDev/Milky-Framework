<?php

namesapce Penoaks\Routing\Events;

class RouteMatched
{
	/**
	 * The route instance.
	 *
	 * @var \Penoaks\Routing\Route
	 */
	public $route;

	/**
	 * The request instance.
	 *
	 * @var \Penoaks\Http\Request
	 */
	public $request;

	/**
	 * Create a new event instance.
	 *
	 * @param  \Penoaks\Routing\Route  $route
	 * @param  \Penoaks\Http\Request  $request
	 * @return void
	 */
	public function __construct($route, $request)
	{
		$this->route = $route;
		$this->request = $request;
	}
}
