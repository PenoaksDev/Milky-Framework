<?php

namespace Penoaks\Support\Facades;

/**
 * @see \Penoaks\Routing\UrlGenerator
 */
class URL extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'url';
	}
}
