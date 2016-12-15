<?php namespace Milky\Facades;

use Milky\Account\AccountManager;
use Milky\Account\Guards\Guard;
use Milky\Account\Types\Account;
use Milky\Auth\StatefulGuard;
use Milky\Binding\UniversalBuilder;

/**
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class Acct extends BaseFacade
{
	protected function __getResolver()
	{
		return Guard::class;
	}

	/**
	 * @return AccountManager
	 */
	public static function mgr()
	{
		return UniversalBuilder::resolveClass( AccountManager::class );
	}

	/**
	 * @param null $guard
	 * @return Guard|StatefulGuard
	 */
	public static function guard( $guard = null )
	{
		return static::mgr()->guard( $guard );
	}

	/**
	 * @param null $id
	 * @return Account
	 */
	public static function acct( $id = null )
	{
		if ( is_null( $id ) )
			return static::__do( 'acct', [] );

		return static::getAuth()->retrieveById( $id );
	}

	public static function check()
	{
		return static::isAuthenticated();
	}

	public static function isGuest()
	{
		return !static::isAuthenticated();
	}

	public static function isAdmin()
	{
		return false; // TODO Implement node permissions
	}

	public static function isOp()
	{
		return false; // TODO implement OP feature -- OPs are authorized on all permission nodes
	}

	public static function getDisplayName()
	{
		return static::acct()->getDisplayName();
	}

	public static function getAuth()
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function isAuthenticated()
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function id()
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Attempt to authenticate a acct using the given credentials.
	 *
	 * @param  array $credentials
	 * @param  bool $remember
	 * @param  bool $login
	 * @return bool
	 */
	public static function attempt( array $credentials = [], $remember = false, $login = true )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Log a acct into the application without sessions or cookies.
	 *
	 * @param  array $credentials
	 * @return bool
	 */
	public static function once( array $credentials = [] )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Log a acct into the application.
	 *
	 * @param  Account $acct
	 * @param  bool $remember
	 */
	public static function login( Account $acct, $remember = false )
	{
		static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Log the given acct ID into the application.
	 *
	 * @param  mixed $id
	 * @param  bool $remember
	 * @return Account
	 */
	public static function loginUsingId( $id, $remember = false )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Log the given acct ID into the application without sessions or cookies.
	 *
	 * @param  mixed $id
	 * @return bool
	 */
	public static function onceUsingId( $id )
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Determine if the acct was authenticated via "remember me" cookie.
	 *
	 * @return bool
	 */
	public static function viaRemember()
	{
		return static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	/**
	 * Log the acct out of the application.
	 */
	public static function logout()
	{
		static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}
}
