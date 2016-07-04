<?php

namespace Foundation\Session;

use Foundation\Support\Manager;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NullSessionHandler;

class SessionManager extends Manager
{
	/**
	 * Call a custom driver creator.
	 *
	 * @param  string  $driver
	 * @return mixed
	 */
	protected function callCustomCreator($driver)
	{
		return $this->buildSession(parent::callCustomCreator($driver));
	}

	/**
	 * Create an instance of the "array" session driver.
	 *
	 * @return \Foundation\Session\Store
	 */
	protected function createArrayDriver()
	{
		return $this->buildSession(new NullSessionHandler);
	}

	/**
	 * Create an instance of the "cookie" session driver.
	 *
	 * @return \Foundation\Session\Store
	 */
	protected function createCookieDriver()
	{
		$lifetime = $this->fw->bindings['config']['session.lifetime'];

		return $this->buildSession(new CookieSessionHandler($this->fw->bindings['cookie'], $lifetime));
	}

	/**
	 * Create an instance of the file session driver.
	 *
	 * @return \Foundation\Session\Store
	 */
	protected function createFileDriver()
	{
		return $this->createNativeDriver();
	}

	/**
	 * Create an instance of the file session driver.
	 *
	 * @return \Foundation\Session\Store
	 */
	protected function createNativeDriver()
	{
		$path = $this->fw->bindings['config']['session.files'];

		$lifetime = $this->fw->bindings['config']['session.lifetime'];

		return $this->buildSession(new FileSessionHandler($this->fw->bindings['files'], $path, $lifetime));
	}

	/**
	 * Create an instance of the database session driver.
	 *
	 * @return \Foundation\Session\Store
	 */
	protected function createDatabaseDriver()
	{
		$connection = $this->getDatabaseConnection();

		$table = $this->fw->bindings['config']['session.table'];

		$lifetime = $this->fw->bindings['config']['session.lifetime'];

		return $this->buildSession(new DatabaseSessionHandler($connection, $table, $lifetime, $this->fw));
	}

	/**
	 * Create an instance of the legacy database session driver.
	 *
	 * @return \Foundation\Session\Store
	 *
	 * @deprecated since version 5.2.
	 */
	protected function createLegacyDatabaseDriver()
	{
		$connection = $this->getDatabaseConnection();

		$table = $this->fw->bindings['config']['session.table'];

		$lifetime = $this->fw->bindings['config']['session.lifetime'];

		return $this->buildSession(new LegacyDatabaseSessionHandler($connection, $table, $lifetime));
	}

	/**
	 * Get the database connection for the database driver.
	 *
	 * @return \Foundation\Database\Connection
	 */
	protected function getDatabaseConnection()
	{
		$connection = $this->fw->bindings['config']['session.connection'];

		return $this->fw->bindings['db']->connection($connection);
	}

	/**
	 * Create an instance of the APC session driver.
	 *
	 * @return \Foundation\Session\Store
	 */
	protected function createApcDriver()
	{
		return $this->createCacheBased('apc');
	}

	/**
	 * Create an instance of the Memcached session driver.
	 *
	 * @return \Foundation\Session\Store
	 */
	protected function createMemcachedDriver()
	{
		return $this->createCacheBased('memcached');
	}

	/**
	 * Create an instance of the Wincache session driver.
	 *
	 * @return \Foundation\Session\Store
	 */
	protected function createWincacheDriver()
	{
		return $this->createCacheBased('wincache');
	}

	/**
	 * Create an instance of the Redis session driver.
	 *
	 * @return \Foundation\Session\Store
	 */
	protected function createRedisDriver()
	{
		$handler = $this->createCacheHandler('redis');

		$handler->getCache()->getStore()->setConnection($this->fw->bindings['config']['session.connection']);

		return $this->buildSession($handler);
	}

	/**
	 * Create an instance of a cache driven driver.
	 *
	 * @param  string  $driver
	 * @return \Foundation\Session\Store
	 */
	protected function createCacheBased($driver)
	{
		return $this->buildSession($this->createCacheHandler($driver));
	}

	/**
	 * Create the cache based session handler instance.
	 *
	 * @param  string  $driver
	 * @return \Foundation\Session\CacheBasedSessionHandler
	 */
	protected function createCacheHandler($driver)
	{
		$minutes = $this->fw->bindings['config']['session.lifetime'];

		return new CacheBasedSessionHandler(clone $this->fw->bindings['cache']->driver($driver), $minutes);
	}

	/**
	 * Build the session instance.
	 *
	 * @param  \SessionHandlerInterface  $handler
	 * @return \Foundation\Session\Store
	 */
	protected function buildSession($handler)
	{
		if ($this->fw->bindings['config']['session.encrypt'])
{
			return new EncryptedStore(
				$this->fw->bindings['config']['session.cookie'], $handler, $this->fw->bindings['encrypter']
			);
		}
else
{
			return new Store($this->fw->bindings['config']['session.cookie'], $handler);
		}
	}

	/**
	 * Get the session configuration.
	 *
	 * @return array
	 */
	public function getSessionConfig()
	{
		return $this->fw->bindings['config']['session'];
	}

	/**
	 * Get the default session driver name.
	 *
	 * @return string
	 */
	public function getDefaultDriver()
	{
		return $this->fw->bindings['config']['session.driver'];
	}

	/**
	 * Set the default session driver name.
	 *
	 * @param  string  $name
	 * @return void
	 */
	public function setDefaultDriver($name)
	{
		$this->fw->bindings['config']['session.driver'] = $name;
	}
}
