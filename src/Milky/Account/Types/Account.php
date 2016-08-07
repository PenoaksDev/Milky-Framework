<?php namespace Milky\Account\Types;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
interface Account extends \ArrayAccess
{
	/**
	 * Compiles a human readable display name, e.g., John Smith
	 *
	 * @return string A human readable display name
	 */
	public function getDisplayName();

	/**
	 * Returns the AcctId for this Account
	 *
	 * @return string Account Id
	 */
	public function getId();

	/**
	 * Get the password for the user.
	 *
	 * @return string
	 */
	public function getAuthPassword();

	/**
	 * @return string
	 */
	public function getRememberToken();

	public function setRememberToken( $token );

	public function save();

	public function isActivated();
}
