<?php namespace Milky\Facades;

/**
 * @see \Milky\Auth\AuthManager
 */
class Auth extends BaseFacade
{
	protected function __getResolver()
	{
		return 'auth.mgr';
	}
}
