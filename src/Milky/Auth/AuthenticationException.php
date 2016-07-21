<?php namespace Milky\Auth;

use Exception;

class AuthenticationException extends Exception
{
	/**
	 * The guard instance.
	 *
	 * @var Guard
	 */
	protected $guard;

	/**
	 * Create a new authentication exception.
	 *
	 * @param Guard|null $guard
	 */
	public function __construct( $guard = null )
	{
		$this->guard = $guard;

		parent::__construct( 'Unauthenticated.' );
	}

	/**
	 * Get the guard instance.
	 *
	 * @return Guard|null
	 */
	public function guard()
	{
		return $this->guard;
	}
}
