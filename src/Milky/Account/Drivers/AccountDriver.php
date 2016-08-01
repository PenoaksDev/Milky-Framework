<?php namespace Milky\Account\Drivers;

use Milky\Account\Account;
use Milky\Account\Auths\AccountAuth;
use Milky\Exceptions\AuthenticationException;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
abstract class AccountDriver
{
	/**
	 * The currently logged in account
	 *
	 * @var Account
	 */
	protected $acct;

	/**
	 * The Account authenticator
	 *
	 * @var AccountAuth
	 */
	protected $auth;

	/**
	 * AccountDriver constructor.
	 *
	 * @param AccountAuth $auth
	 */
	public function __construct( AccountAuth $auth )
	{
		$this->auth = $auth;
	}

	/**
	 * Determine if the current account is authenticated.
	 *
	 * @return Account
	 *
	 * @throws AuthenticationException
	 */
	public function isAuthenticatedWithException()
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
	 * Get the ID for the currently authenticated account.
	 *
	 * @return int|null
	 */
	public function id()
	{
		if ( $this->acct() )
			return $this->acct()->getId();
	}

	/**
	 * Set the current account
	 *
	 * @param Account $acct
	 * @return $this
	 */
	public function setAccount( $acct )
	{
		$this->acct = $acct;

		return $this;
	}

	/**
	 * @return Account
	 */
	public abstract function acct();

	/**
	 * Validate an account's credentials.
	 *
	 * @param  array $credentials
	 * @return bool
	 */
	public abstract function validate( array $credentials = [] );
}
