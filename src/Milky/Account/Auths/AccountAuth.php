<?php namespace Milky\Account\Auths;

use Milky\Account\Types\Account;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
interface AccountAuth
{
	/**
	 * Retrieve an account by their unique identifier.
	 *
	 * @param  mixed $identifier
	 * @return Account|null
	 */
	public function retrieveById( $identifier );

	/**
	 * Retrieve an account by their unique identifier and "remember me" token.
	 *
	 * @param  mixed $identifier
	 * @param  string $token
	 * @return Account|null
	 */
	public function retrieveByToken( $identifier, $token );

	/**
	 * Update the "remember me" token for the given account in storage.
	 *
	 * @param  Account $acct
	 * @param  string $token
	 * @return void
	 */
	public function updateRememberToken( Account $acct, $token );

	/**
	 * Retrieve an account by the given credentials.
	 *
	 * @param  array $credentials
	 * @return Account|null
	 */
	public function retrieveByCredentials( array $credentials );

	/**
	 * Validate an account against the given credentials.
	 *
	 * @param  Account $acct
	 * @param  array $credentials
	 * @return bool
	 */
	public function validateCredentials( Account $acct, array $credentials );
}
