<?php namespace Milky\Account\Guards;

use Milky\Account\Types\Account;

abstract class StatefulGuard extends Guard
{
	/**
	 * Attempt to authenticate a acct using the given credentials.
	 *
	 * @param  array $credentials
	 * @param  bool $remember
	 * @param  bool $login
	 * @return bool
	 */
	public abstract function attempt( array $credentials = [], $remember = false, $login = true );

	/**
	 * Log a acct into the application without sessions or cookies.
	 *
	 * @param  array $credentials
	 * @return bool
	 */
	public abstract function once( array $credentials = [] );

	/**
	 * Log a acct into the application.
	 *
	 * @param  Account $acct
	 * @param  bool $remember
	 * @return void
	 */
	public abstract function login( Account $acct, $remember = false );

	/**
	 * Log the given acct ID into the application.
	 *
	 * @param  mixed $id
	 * @param  bool $remember
	 * @return Account
	 */
	public abstract function loginUsingId( $id, $remember = false );

	/**
	 * Log the given acct ID into the application without sessions or cookies.
	 *
	 * @param  mixed $id
	 * @return bool
	 */
	public abstract function onceUsingId( $id );

	/**
	 * Determine if the acct was authenticated via "remember me" cookie.
	 *
	 * @return bool
	 */
	public abstract function viaRemember();

	/**
	 * Log the acct out of the application.
	 *
	 * @return void
	 */
	public abstract function logout();
}
