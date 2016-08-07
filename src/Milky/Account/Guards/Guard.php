<?php namespace Milky\Account\Guards;

use Milky\Account\Auths\AccountAuth;
use Milky\Account\Types\Account;
use Milky\Exceptions\Auth\AuthenticationException;

abstract class Guard
{
	/**
	 * The currently authenticated user.
	 *
	 * @var Account
	 */
	protected $acct;

	/**
	 * The user provider implementation.
	 *
	 * @var AccountAuth
	 */
	protected $auth;

	public function __construct( AccountAuth $auth = null )
	{
		$this->auth = $auth;
	}

	/**
	 * Get the default Guard Name
	 *
	 * @return string
	 */
	public abstract function name();

	/**
	 * Get the currently authenticated user.
	 *
	 * @return Account|null
	 */
	public abstract function acct();

	/**
	 * Validate a user's credentials.
	 *
	 * @param  array $credentials
	 * @return bool
	 */
	public abstract function validate( array $credentials = [] );

	/**
	 * @param AccountAuth $auth
	 */
	public function setAuth( $auth )
	{
		$this->auth = $auth;
	}

	/**
	 * @return AccountAuth
	 */
	public function getAuth()
	{
		return $this->auth;
	}

	/**
	 * Determine if the current user is authenticated.
	 *
	 * @return Account
	 *
	 * @throws AuthenticationException
	 */
	public function authenticate()
	{
		if ( !is_null( $acct = $this->acct() ) )
			return $acct;

		throw new AuthenticationException( $this );
	}

	/**
	 * Determine if the current account is authenticated.
	 *
	 * @return bool
	 */
	public function isAuthenticated()
	{
		return !is_null( $this->acct() );
	}

	/**
	 * Determine if the current user is authenticated.
	 *
	 * @return bool
	 */
	public function check()
	{
		return !is_null( $this->acct() );
	}

	/**
	 * Determine if the current user is a guest.
	 *
	 * @return bool
	 */
	public function guest()
	{
		return !$this->check();
	}

	/**
	 * Get the ID for the currently authenticated user.
	 *
	 * @return int|null
	 */
	public function id()
	{
		if ( $this->acct() )
			return $this->acct()->getId();

		return null;
	}

	/**
	 * Set the current user.
	 *
	 * @param  Account $acct
	 * @return $this
	 */
	public function setAcct( Account $acct )
	{
		$this->acct = $acct;

		return $this;
	}
}
