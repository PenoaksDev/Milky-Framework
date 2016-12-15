<?php namespace Milky\Http\Session;

use Carbon\Carbon;
use Closure;
use Milky\Binding\UniversalBuilder;
use Milky\Cache\CacheManager;
use Milky\Cache\RedisStore;
use Milky\Database\Connection;
use Milky\Database\DatabaseManager;
use Milky\Encryption\Encrypter;
use Milky\Exceptions\FrameworkException;
use Milky\Facades\Config;
use Milky\Filesystem\Filesystem;
use Milky\Http\Cookies\CookieJar;
use Milky\Http\Request;
use Milky\Http\Session\Handlers\CacheBasedSessionHandler;
use Milky\Http\Session\Handlers\CookieSessionHandler;
use Milky\Http\Session\Handlers\DatabaseSessionHandler;
use Milky\Http\Session\Handlers\FileSessionHandler;
use SessionHandlerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NullSessionHandler;

/**
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class SessionManager
{
	private $configPrefix = 'session';

	/**
	 * The currently active session drivers
	 *
	 * @var Store[]
	 */
	protected $drivers = [];

	/**
	 * Indicates if the session was handled for the current request.
	 *
	 * @var bool
	 */
	protected $sessionHandled = false;

	/**
	 * @return SessionManager
	 */
	public static function i()
	{
		return UniversalBuilder::resolve( static::class );
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  Request $request
	 * @param  \Closure $next
	 * @return mixed
	 */
	public function handle( $request, Closure $next )
	{
		$this->sessionHandled = true;
		$session = null;

		// If a session driver has been configured, we will need to start the session here
		// so that the data is ready for an application. Note that the framework sessions
		// do not make use of PHP "native" sessions in any way since they are crappy.
		if ( $this->sessionConfigured() )
		{
			$session = $this->startSession( $request );
			$request->setSession( $session );
		}

		$response = $next( $request );

		// Again, if the session has been configured we will need to close out the session
		// so that the attributes may be persisted to some storage medium. We will also
		// add the session identifier cookie to the application response headers now.
		if ( $this->sessionConfigured() )
		{
			$this->storeCurrentUrl( $request, $session );
			$this->collectGarbage( $session );
			$this->addCookieToResponse( $response, $session );
		}

		return $response;
	}

	/**
	 * Start the session for the given request.
	 *
	 * @param  Request $request
	 * @return SessionInterface
	 */
	protected function startSession( Request $request )
	{
		$session = $this->getSession( $request );
		$session->setRequestOnHandler( $request );
		$session->start();

		return $session;
	}

	/**
	 * Get the session implementation from the manager.
	 *
	 * @param  Request $request
	 * @return SessionInterface
	 */
	public function getSession( Request $request )
	{
		$session = $this->driver();
		$session->setId( $request->cookies->get( $session->getName() ) );

		return $session;
	}


	/**
	 * Get the database connection for the database driver.
	 *
	 * @return Connection
	 */
	protected function getDatabaseConnection()
	{
		return DatabaseManager::i()->connection( Config::get( $this->configPrefix . '.connection' ) );
	}


	/**
	 * Perform any final actions for the request lifecycle.
	 *
	 * @param  Request $request
	 * @param  Response $response
	 */
	public function terminate( $request, $response )
	{
		if ( $this->sessionHandled && $this->sessionConfigured() && !$this->usingHandler( CookieSessionHandler::class ) )
			$this->driver()->save();
	}

	/**
	 * Store the current URL for the request if necessary.
	 *
	 * @param  Request $request
	 * @param  SessionInterface $session
	 */
	protected function storeCurrentUrl( Request $request, $session )
	{
		if ( $request->method() === 'GET' && $request->route() && !$request->ajax() )
			$session->setPreviousUrl( $request->fullUrl() );
	}

	/**
	 * Remove the garbage from the session if necessary.
	 *
	 * @param  SessionInterface $session
	 */
	protected function collectGarbage( SessionInterface $session )
	{
		// Here we will see if this request hits the garbage collection lottery by hitting
		// the odds needed to perform garbage collection on any given request. If we do
		// hit it, we'll call this handler to let it delete all the expired sessions.
		if ( $this->configHitsLottery() )
			$session->getHandler()->gc( $this->getSessionLifetimeInSeconds() );
	}

	/**
	 * Determine if the configuration odds hit the lottery.
	 *
	 * @return bool
	 */
	protected function configHitsLottery()
	{
		$config = Config::get( $this->configPrefix . '.lottery' );
		return random_int( 1, $config[1] ) <= $config[0];
	}

	/**
	 * Add the session cookie to the application response.
	 *
	 * @param  Response $response
	 * @param  SessionInterface $session
	 */
	protected function addCookieToResponse( Response $response, SessionInterface $session )
	{
		if ( $this->usingHandler( CookieSessionHandler::class ) )
			$this->driver()->save();

		if ( $this->sessionIsPersistent() )
			$response->headers->setCookie( new Cookie( $session->getName(), $session->getId(), $this->getCookieExpirationDate(), Config::get( $this->configPrefix . '.path' ), Config::get( $this->configPrefix . '.domain' ), Config::get( $this->configPrefix . '.secure', false ), Config::get( $this->configPrefix . '.http_only', true ) ) );
	}

	/**
	 * Get the session lifetime in seconds.
	 *
	 * @return int
	 */
	protected function getSessionLifetimeInSeconds()
	{
		return Config::get( $this->configPrefix . '.lifetime' ) * 60;
	}

	/**
	 * Get the cookie lifetime in seconds.
	 *
	 * @return int|Carbon
	 */
	protected function getCookieExpirationDate()
	{
		return Config::get( $this->configPrefix . '.expire_on_close' ) ? 0 : Carbon::now()->addMinutes( Config::get( $this->configPrefix . '.lifetime' ) );
	}

	/**
	 * Determine if a session driver has been configured.
	 *
	 * @return bool
	 */
	protected function sessionConfigured()
	{
		return Config::has( 'session.driver' );
	}

	/**
	 * Determine if the configured session driver is persistent.
	 *
	 * @param  array|null $config
	 * @return bool
	 */
	protected function sessionIsPersistent()
	{
		return !in_array( $this->getDefaultDriver(), [null, 'array'] );
	}

	public function usingHandler( $class )
	{
		if ( !is_subclass_of( $class, SessionHandlerInterface::class, true ) )
			throw new FrameworkException( "The session handler [" . $class . "] must implement [" . SessionHandlerInterface::class . "]" );
		if ( !$this->sessionConfigured() )
			return false;

		return is_subclass_of( $this->driver()->getHandler(), $class );
	}

	public function loadDriver( $name, $driver )
	{
		$name = strtolower( $name );
		if ( array_key_exists( $name, $this->drivers ) || in_array( $driver, $this->drivers ) )
			throw new FrameworkException( "Session driver [" . $name . "] is already loaded" );
		$this->drivers[$name] = $driver;
	}

	/**
	 * Get a driver instance.
	 *
	 * @param string $driver
	 * @return Store
	 */
	public function driver( $driver = null )
	{
		$driver = strtolower( $driver ?: $this->getDefaultDriver() );

		// If the given driver has not been created before, we will create the instances
		// here and cache it so we can return it next time very quickly. If there is
		// already a driver created by this name, we'll just return that instance.
		if ( !array_key_exists( $driver, $this->drivers ) )
			$this->drivers[$driver] = $this->createDriver( $driver );

		return $this->drivers[$driver];
	}

	/**
	 * @param string $driver
	 * @return Store
	 */
	private function createDriver( $driver )
	{
		switch ( $driver )
		{
			case 'apc':
			case 'memcached':
			case 'wincache':
				return $this->buildSession( $this->createCacheHandler( $driver ) );
			case 'redis':
			{
				$handler = $this->createCacheHandler( 'redis' );
				if ( !$handler->getCache()->getStore() instanceof RedisStore )
					throw new FrameworkException( "You must use the [" . RedisStore::class . "] cache store to use the redis session driver." );
				$handler->getCache()->getStore()->setConnection( Config::get( $this->configPrefix . '.connection' ) );

				return $this->buildSession( $handler );
			}
			case 'array':
				return $this->buildSession( new NullSessionHandler );
			case 'cookie':
				return $this->buildSession( new CookieSessionHandler( CookieJar::i(), Config::get( $this->configPrefix . '.lifetime' ) ) );
			case 'file':
			case 'native':
				return $this->buildSession( new FileSessionHandler( Filesystem::i(), Config::get( $this->configPrefix . '.files' ), Config::get( $this->configPrefix . '.lifetime' ) ) );
			case 'database':
				return $this->buildSession( new DatabaseSessionHandler( $this->getDatabaseConnection(), Config::get( $this->configPrefix . '.table' ), Config::get( $this->configPrefix . '.lifetime' ) ) );
			default:
				throw new \InvalidArgumentException( "Driver [$driver] not supported." );
		}
	}

	/**
	 * Create the cache based session handler instance.
	 *
	 * @param  string $driver
	 * @return CacheBasedSessionHandler
	 */
	protected function createCacheHandler( $driver )
	{
		return new CacheBasedSessionHandler( clone CacheManager::i()->driver( $driver ), Config::get( $this->configPrefix . '.lifetime' ) );
	}

	/**
	 * Build the session instance.
	 *
	 * @param  SessionHandlerInterface $handler
	 * @return Store
	 */
	protected function buildSession( $handler )
	{
		if ( Config::get( $this->configPrefix . '.encrypt' ) )
			return new EncryptedStore( Config::get( $this->configPrefix . '.cookie' ), $handler, Encrypter::i() );
		else
			return new Store( Config::get( $this->configPrefix . '.cookie' ), $handler );
	}

	/**
	 * Get the default session driver name.
	 *
	 * @return string
	 */
	public function getDefaultDriver()
	{
		return Config::get( 'session.driver' );
	}

	/**
	 * Set the default session driver name.
	 *
	 * @param  string $name
	 */
	public function setDefaultDriver( $name )
	{
		Config::set( 'session.driver', $name );;
	}

	/**
	 * Dynamically call the default driver instance.
	 *
	 * @param  string $method
	 * @param  array $parameters
	 * @return mixed
	 */
	public function __call( $method, $parameters )
	{
		return call_user_func_array( [$this->driver(), $method], $parameters );
	}
}
