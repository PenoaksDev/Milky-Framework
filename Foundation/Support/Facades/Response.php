<?php

namespace Foundation\Support\Facades;

/**
 * @see \Foundation\Contracts\Routing\ResponseFactory
 */
class Response extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'Foundation\Contracts\Routing\ResponseFactory';
	}
}
