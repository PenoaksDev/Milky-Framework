<?php

namespace Penoaks\Support\Facades;

/**
 * @see \Penoaks\Routing\Router
 */
class Route extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'router';
	}
}
