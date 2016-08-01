<?php namespace Milky\Facades;

use Milky\Account\AccountManager;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class Acct extends BaseFacade
{
	protected function __getResolver()
	{
		return static::auth()->driver()->acct();
	}

	private static function auth()
	{
		return AccountManager::i();
	}

	public static function acct( $id = null )
	{
		if ( is_null( $id ) )
			return AccountManager::i()->auth()->retrieveById( $id );
		else
			return AccountManager::i()->driver()->acct();
	}

	public static function check()
	{
		return static::auth()->driver()->isAuthenticated();
	}

	public static function isGuest()
	{
		return !static::auth()->driver()->isAuthenticated();
	}

	public static function isAdmin()
	{
		return false; // TODO Implement node permissions
	}

	public static function isOp()
	{
		return false; // TODO implement OP feature -- OPs are authorized on all permission nodes
	}
}
