<?php namespace Milky\Account\Drivers;

use Milky\Account\Account;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
interface StatefulDriver
{
	/**
	 * Attempt to authenticate an account using the given credentials.
	 *
	 * @param  array $credentials
	 * @param  bool $remember
	 * @param  bool $login
	 * @return bool
	 */
	public function attempt( array $credentials = [], $remember = false, $login = true );

	/**
	 * Log an account into the application without sessions or cookies.
	 *
	 * @param  array $credentials
	 * @return bool
	 */
	public function once( array $credentials = [] );

	/**
	 * Log an account into the application.
	 *
	 * @param  Account $acct
	 * @param  bool $remember
	 * @return void
	 */
	public function login( Account $acct, $remember = false );

	/**
	 * Log the given account ID into the application.
	 *
	 * @param  mixed $id
	 * @param  bool $remember
	 * @return Account
	 */
	public function loginUsingId( $id, $remember = false );

	/**
	 * Log the given account ID into the application without sessions or cookies.
	 *
	 * @param  mixed $id
	 * @return bool
	 */
	public function onceUsingId( $id );

	/**
	 * Determine if the account was authenticated via "remember me" cookie.
	 *
	 * @return bool
	 */
	public function viaRemember();

	/**
	 * Log the account out of the application.
	 *
	 * @return void
	 */
	public function logout();
}
