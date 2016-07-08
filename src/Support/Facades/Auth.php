<?php

namesapce Penoaks\Support\Facades;

/**
 * @see \Penoaks\Auth\AuthManager
 * @see \Penoaks\Contracts\Auth\Factory
 * @see \Penoaks\Contracts\Auth\Guard
 * @see \Penoaks\Contracts\Auth\StatefulGuard
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
