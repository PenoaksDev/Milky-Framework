<?php

namespace Foundation\Support\Facades;

/**
 * @see \Foundation\Auth\AuthManager
 * @see \Foundation\Contracts\Auth\Factory
 * @see \Foundation\Contracts\Auth\Guard
 * @see \Foundation\Contracts\Auth\StatefulGuard
 */
class Auth extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'auth';
	}
}
