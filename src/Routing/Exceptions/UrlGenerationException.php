<?php

namesapce Penoaks\Routing\Exceptions;

use Exception;

class UrlGenerationException extends Exception
{
	/**
	 * Create a new exception for missing route parameters.
	 *
	 * @param  \Penoaks\Routing\Route  $route
	 * @return static
	 */
	public static function forMissingParameters($route)
	{
		return new static("Missing required parameters for [Route: {$route->getName()}] [URI: {$route->getPath()}].");
	}
}
