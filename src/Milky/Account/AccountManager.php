<?php namespace Milky\Account;

use Milky\Account\Auths\AccountAuth;
use Milky\Account\Auths\DatabaseAuth;
use Milky\Account\Auths\EloquentAuth;
use Milky\Account\Drivers\AccountDriver;
use Milky\Account\Drivers\SessionDriver;
use Milky\Account\Drivers\TokenDriver;
use Milky\Account\Types\EloquentAccount;
use Milky\Binding\BindingBuilder;
use Milky\Framework;
use Milky\Helpers\Func;
use Milky\Http\Factory;
use Milky\Http\Session\SessionManager;
use Milky\Services\ServiceFactory;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class AccountManager extends ServiceFactory
{
	/**
	 * The Account auth handler
	 *
	 * @var AccountAuth
	 */
	private $auth;

	/**
	 * The Account storage driver
	 *
	 * @var AccountDriver
	 */
	private $driver;

	/**
	 * AccountManager constructor.
	 *
	 * @param AccountAuth $auth
	 * @param string $driver
	 */
	public function __construct( $auth = null, $driver = null )
	{
		parent::__construct();

		if ( is_null( $auth ) || is_string( $auth ) )
			$this->resolveAuth( $auth ?: $this->getDefaultAuth() );
		else
			$this->auth = $auth;

		if ( is_null( $driver ) || is_string( $driver ) )
			$this->resolveDriver( $driver ?: $this->getDefaultDriver() );
		else
			$this->driver = $driver;
	}

	protected function resolveAuth( $auth )
	{
		$config = Framework::config()->get( 'acct.auths.' . $auth );

		switch ( $auth )
		{
			case 'database':
			{
				$this->auth = new DatabaseAuth( $config['table'] );
				break;
			}
			case 'eloquent':
			{
				$this->auth = new EloquentAuth( $config['usrModel'], $config['grpModel'] );
				EloquentAccount::setConfig( $config );
				break;
			}
			default:
				throw new \InvalidArgumentException( "Account authentication [{$auth}] is not defined." );
		}
	}

	/**
	 * @param string $class
	 */
	protected function resolveDriver( $class )
	{
		$driver = null;
		switch( strtolower( $class ) )
		{
			case 'session':
			{
				$driver = new SessionDriver( $this->auth, SessionManager::i()->driver(), Factory::i()->request() );
				break;
			}
			case 'token':
			{
				$driver = new TokenDriver( $this->auth, Factory::i()->request() );
				break;
			}
			default:
			{
				$driver = BindingBuilder::buildBinding( $class );
				if ( !is_subclass_of( $driver, AccountDriver::class ) )
					throw new \InvalidArgumentException( "The account driver [" . $class . "] must extend the AccountDriver class" );
			}
		}

		$this->driver = $driver;
	}

	public function getDefaultAuth()
	{
		return Framework::config()->get( 'acct.defaults.auth', 'database' );
	}

	public function getDefaultDriver()
	{
		return Framework::config()->get( 'acct.defaults.driver', 'session' );
	}

	public function generateAcctId( $seed )
	{
		$acctId = "";

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
		while ( $this->auth->retrieveById( $acctId ) != null );

		return $acctId;
	}

	public function auth()
	{
		return $this->auth;
	}

	public function driver()
	{
		return $this->driver;
	}

	/**
	 * Get the account configuration.
	 *
	 * @param string $name
	 * @return array
	 */
	protected function getConfig( $name )
	{
		return Framework::config()->get( 'acct.' . $name );
	}
}
