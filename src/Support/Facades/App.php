<?php

namespace Penoaks\Support\Facades;

/**
 * @see \Penoaks\Framework
 */
class App extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'fw';
	}
}
