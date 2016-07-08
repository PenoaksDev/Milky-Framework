<?php

namespace Penoaks\Support\Facades;

/**
 * @see \Penoaks\Session\SessionManager
 * @see \Penoaks\Session\Store
 */
class Session extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'session';
	}
}
