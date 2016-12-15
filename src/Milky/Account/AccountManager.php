<?php namespace Milky\Account;

use Milky\Account\Auths\AccountAuth;
use Milky\Account\Auths\DatabaseAuth;
use Milky\Account\Auths\EloquentAuth;
use Milky\Account\Guards\Guard;
use Milky\Account\Guards\RequestGuard;
use Milky\Account\Guards\SessionGuard;
use Milky\Account\Guards\StatefulGuard;
use Milky\Account\Guards\TokenGuard;
use Milky\Account\Types\EloquentAccount;
use Milky\Binding\UniversalBuilder;
use Milky\Exceptions\Auth\AccountException;
use Milky\Facades\Config;
use Milky\Helpers\Func;
use Milky\Http\Cookies\CookieJar;
use Milky\Http\Request;
use Milky\Http\Session\SessionManager;
use Milky\Impl\Extendable;

/**
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class AccountManager
{
	use Extendable;

	/**
	 * @var Guard[]
	 */
	private $guards = [];

	/**
	 * @return AccountManager
	 */
	public static function i()
	{
		return UniversalBuilder::resolve( 'account.mgr' );
	}

	/**
	 * Attempt to get the guard from the local cache.
	 *
	 * @param string $name
	 * @return Guard|StatefulGuard
	 */
	public function guard( $name = null )
	{
		$name = $name ?: $this->getDefaultGuard();

		return array_key_exists( $name, $this->guards ) ? $this->guards[$name] : $this->guards[$name] = $this->resolveGuard( $name );
	}

	/**
	 * Resolve the implemented Account Guard
	 *
	 * @param string $name
	 */
	public function resolveGuard( $name )
	{
		$config = Config::get( 'auth.guards.' . $name );

		if ( is_null( $config ) )
			throw new AccountException( "Auth guard [" . $name . "] is not defined in the configuration" );

		$uses = $config['uses'];
		$auth = $this->resolveAuth( $config['auth'] ?: $this->getDefaultAuth() );

		$guard = null;
		switch ( $uses )
		{
			case 'session':
			{
				$guard = new SessionGuard( $name, $auth, SessionManager::i()->driver(), Request::i(), CookieJar::i() );
				break;
			}
			case 'token':
			{
				$guard = new TokenGuard( $auth, Request::i() );
				break;
			}
			default:
			{
				$guard = UniversalBuilder::resolve( $uses );

				if ( is_null( $guard ) )
					throw new AccountException( "We could not resolve the guard [" . $uses . "]" );

				if ( !is_subclass_of( $guard, Guard::class ) )
					throw new \InvalidArgumentException( "The guard [" . $uses . "] must extend the [" . Guard::class . "] class" );
			}
		}

		return $guard;
	}

	/**
	 * Resolve the implemented Account Auth
	 *
	 * @param string $auth
	 * @return AccountAuth
	 */
	public function resolveAuth( $auth )
	{
		$config = Config::get( 'auth.auths.' . $auth );
		$uses = $config['uses'];

		switch ( $uses )
		{
			case 'database':
			{
				$auth = new DatabaseAuth( $config['table'] );
				break;
			}
			case 'eloquent':
			{
				$auth = new EloquentAuth( $config['usrModel'], $config['grpModel'] );
				break;
			}
			default:
			{
				$auth = UniversalBuilder::resolve( $uses );

				if ( is_null( $auth ) )
					throw new AccountException( "We could not resolve the auth [" . $uses . "]" );

				if ( !is_subclass_of( $auth, AccountAuth::class ) )
					throw new \InvalidArgumentException( "The auth [" . $uses . "] must extend the [" . AccountAuth::class . "] class" );
			}
		}

		return $auth;
	}

	public function setDefaultGuard( $name )
	{
		Config::set( 'auth.defaults.guard', $name );
	}

	public function setDefaultAuth( $name )
	{
		Config::set( 'auth.defaults.auth', $name );
	}

	public function getDefaultGuard()
	{
		return Config::get( 'auth.defaults.guard', 'web' );
	}

	public function getDefaultAuth()
	{
		return Config::get( 'auth.defaults.auth', 'users' );
	}

	/**
	 * Register a callback based guard
	 *
	 * @param $name
	 * @param callable $callback
	 */
	public function viaRequest( $name, callable $callback )
	{
		$this->guards[$name] = new RequestGuard( $callback, Request::i() );
	}

	public function generateAcctId( $seed )
	{
		if ( empty( $seed ) )
			$acctId = "ab123C";
		else
		{
			$seed = preg_replace( "[\\W\\d]", "", $seed );

			$acctId = strtolower( Func::randomChars( $seed, 2 ) );
			$sum = Func::removeLetters( md5( $seed ) );
			$acctId .= count( $sum ) < 3 ? Func::randomStr( "123" ) : substr( 0, 3, $sum );
			$acctId .= strtoupper( Func::randomChars( $seed, 1 ) );
		}

		if ( empty( $acctId ) )
			$acctId = "ab123C";

		$auth = $this->guard()->getAuth();

		$tries = 1;
		do
		{
			if ( empty( $acctId ) || count( $acctId ) <> 6 || !preg_match( "[a-z]{2}[0-9]{3}[A-Z]", $acctId ) )
				throw new \RuntimeException( "Something went wrong!" );

			// When our tries are divisible by 25 we attempt to randomize the last letter for more chances.
			if ( $tries % 25 == 0 )
				$acctId = substr( 0, 4, $acctId ) . Func::randomStr( substr( 5, $acctId ) );

			$acctId = substr( 0, 2, $acctId ) . Func::randomStr( "123" ) . substr( 5, $acctId );

			$tries++;
		}
		while ( $auth->retrieveById( $acctId ) != null );

		return $acctId;
	}

	public function __call( $method, $parameters )
	{
		// TODO Add method not found exception
		return call_user_func_array( [$this->guard(), $method], $parameters );
	}
}
