<?php namespace Milky\Http\Session;

use Milky\Framework;
use Milky\Impl\Manager;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NullSessionHandler;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class SessionManager extends Manager
{
	/**
	 * Call a custom driver creator.
	 *
	 * @param  string $driver
	 * @return mixed
	 */
	protected function callCustomCreator( $driver )
	{
		return $this->buildSession( parent::callCustomCreator( $driver ) );
	}

	/**
	 * Create an instance of the "array" session driver.
	 *
	 * @return Store
	 */
	protected function createArrayDriver()
	{
		return $this->buildSession( new NullSessionHandler );
	}

	/**
	 * Create an instance of the "cookie" session driver.
	 *
	 * @return Store
	 */
	protected function createCookieDriver()
	{
		$lifetime = Framework::fw()->config->get( 'session.lifetime' );

		return $this->buildSession( new CookieSessionHandler( Framework::fw()->get( 'cookie' ), $lifetime ) );
	}

	/**
	 * Create an instance of the file session driver.
	 *
	 * @return Store
	 */
	protected function createFileDriver()
	{
		return $this->createNativeDriver();
	}

	/**
	 * Create an instance of the file session driver.
	 *
	 * @return Store
	 */
	protected function createNativeDriver()
	{
		$path = Framework::fw()->config->get( 'session.files' );;

		$lifetime = Framework::fw()->config->get( 'session.lifetime' );;

		return $this->buildSession( new FileSessionHandler( Framework::fw()->get( 'files' ), $path, $lifetime ) );
	}

	/**
	 * Create an instance of the database session driver.
	 *
	 * @return Store
	 */
	protected function createDatabaseDriver()
	{
		$connection = $this->getDatabaseConnection();

		$table = Framework::fw()->config->get( 'session.table' );;

		$lifetime = Framework::fw()->config->get( 'session.lifetime' );;

		return $this->buildSession( new DatabaseSessionHandler( $connection, $table, $lifetime ) );
	}

	/**
	 * Get the database connection for the database driver.
	 *
	 * @return Connection
	 */
	protected function getDatabaseConnection()
	{
		$connection = Framework::fw()->config->get( 'session.connection' );;

		return Framework::fw()->get( 'db' )->connection( $connection );
	}

	/**
	 * Create an instance of the APC session driver.
	 *
	 * @return Store
	 */
	protected function createApcDriver()
	{
		return $this->createCacheBased( 'apc' );
	}

	/**
	 * Create an instance of the Memcached session driver.
	 *
	 * @return Store
	 */
	protected function createMemcachedDriver()
	{
		return $this->createCacheBased( 'memcached' );
	}

	/**
	 * Create an instance of the Wincache session driver.
	 *
	 * @return Store
	 */
	protected function createWincacheDriver()
	{
		return $this->createCacheBased( 'wincache' );
	}

	/**
	 * Create an instance of the Redis session driver.
	 *
	 * @return Store
	 */
	protected function createRedisDriver()
	{
		$handler = $this->createCacheHandler( 'redis' );

		$handler->getCache()->getStore()->setConnection( Framework::fw()->config->get( 'session.connection' ) );

		return $this->buildSession( $handler );
	}

	/**
	 * Create an instance of a cache driven driver.
	 *
	 * @param  string $driver
	 * @return Store
	 */
	protected function createCacheBased( $driver )
	{
		return $this->buildSession( $this->createCacheHandler( $driver ) );
	}

	/**
	 * Create the cache based session handler instance.
	 *
	 * @param  string $driver
	 * @return CacheBasedSessionHandler
	 */
	protected function createCacheHandler( $driver )
	{
		$minutes = Framework::fw()->config->get( 'session.lifetime' );;

		return new CacheBasedSessionHandler( clone Framework::fw()->get( 'cache' )->driver( $driver ), $minutes );
	}

	/**
	 * Build the session instance.
	 *
	 * @param  \SessionHandlerInterface $handler
	 * @return Store
	 */
	protected function buildSession( $handler )
	{
		if ( Framework::fw()->config->get( 'session.encrypt' ) )
			return new EncryptedStore( Framework::fw()->config->get( 'session.cookie' ), $handler, Framework::fw()->get( 'encrypter' ) );
		else
			return new Store( Framework::fw()->config->get( 'session.cookie' ), $handler );
	}

	/**
	 * Get the session configuration.
	 *
	 * @return array
	 */
	public function getSessionConfig()
	{
		return Framework::fw()->config->get( 'session' );
	}

	/**
	 * Get the default session driver name.
	 *
	 * @return string
	 */
	public function getDefaultDriver()
	{
		return Framework::fw()->config->get( 'session.driver' );
	}

	/**
	 * Set the default session driver name.
	 *
	 * @param  string $name
	 */
	public function setDefaultDriver( $name )
	{
		Framework::fw()->config->set( 'session.driver', $name );;
	}
}
