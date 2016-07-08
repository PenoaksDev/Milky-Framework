<?php

namesapce Penoaks\Auth;

use Exception;

class AuthenticationException extends Exception
{
	/**
	 * The guard instance.
	 *
	 * @var \Penoaks\Contracts\Auth\Guard
	 */
	protected $guard;

	/**
	 * Create a new authentication exception.
	 *
	 * @param \Penoaks\Contracts\Auth\Guard|null  $guard
	 */
	public function __construct($guard = null)
	{
		$this->guard = $guard;

		parent::__construct('Unauthenticated.');
	}

	/**
	 * Get the guard instance.
	 *
	 * @return \Penoaks\Contracts\Auth\Guard|null
	 */
	public function guard()
	{
		return $this->guard;
	}
}
