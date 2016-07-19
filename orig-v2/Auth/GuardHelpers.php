<?php

namespace Penoaks\Auth;

use Penoaks\Contracts\Auth\Authenticatable as AuthenticatableContract;

/**
 * These methods are typically the same across all guards.
 */
trait GuardHelpers
{
	/**
	 * The currently authenticated user.
	 *
	 * @var \Penoaks\Contracts\Auth\Authenticatable
	 */
	protected $user;

	/**
	 * The user provider implementation.
	 *
	 * @var \Penoaks\Contracts\Auth\UserProvider
	 */
	protected $provider;

	/**
	 * Determine if the current user is authenticated.
	 *
	 * @return \Penoaks\Contracts\Auth\Authenticatable
	 *
	 * @throws \Penoaks\Auth\AuthenticationException
	 */
	public function authenticate()
	{
		if (! is_null($user = $this->user()))
{
			return $user;
		}

		throw new AuthenticationException($this);
	}

	/**
	 * Determine if the current user is authenticated.
	 *
	 * @return bool
	 */
	public function check()
	{
		return ! is_null($this->user());
	}

	/**
	 * Determine if the current user is a guest.
	 *
	 * @return bool
	 */
	public function guest()
	{
		return ! $this->check();
	}

	/**
	 * Get the ID for the currently authenticated user.
	 *
	 * @return int|null
	 */
	public function id()
	{
		if ($this->user())
{
			return $this->user()->getAuthIdentifier();
		}
	}

	/**
	 * Set the current user.
	 *
	 * @param  \Penoaks\Contracts\Auth\Authenticatable  $user
	 * @return $this
	 */
	public function setUser(AuthenticatableContract $user)
	{
		$this->user = $user;

		return $this;
	}
}
