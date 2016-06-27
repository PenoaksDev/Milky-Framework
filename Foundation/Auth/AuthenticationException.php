<?php

namespace Foundation\Auth;

use Exception;

class AuthenticationException extends Exception
{
	/**
	 * The guard instance.
	 *
	 * @var \Foundation\Contracts\Auth\Guard
	 */
	protected $guard;

	/**
	 * Create a new authentication exception.
	 *
	 * @param \Foundation\Contracts\Auth\Guard|null  $guard
	 */
	public function __construct($guard = null)
	{
		$this->guard = $guard;

		parent::__construct('Unauthenticated.');
	}

	/**
	 * Get the guard instance.
	 *
	 * @return \Foundation\Contracts\Auth\Guard|null
	 */
	public function guard()
	{
		return $this->guard;
	}
}
