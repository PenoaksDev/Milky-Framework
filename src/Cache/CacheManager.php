<?php

namesapce Penoaks\Cache;

use Closure;
use Foundation\Support\Arr;
use InvalidArgumentException;
use Foundation\Contracts\Cache\Store;
use Foundation\Contracts\Cache\Factory as FactoryContract;

class CacheManager implements FactoryContract
{
	/**
	 * The application instance.
	 *
	 * @var \Penoaks\Framework
	 */
	protected $fw;

	/**
	 * The array of resolved cache stores.
	 *
	 * @var array
	 */
	protected $stores = [];

	/**
	 * The registered custom driver creators.
	 *
	 * @var array
	 */
	protected $customCreators = [];

	/**
	 * Create a new Cache manager instance.
	 *
	 * @param  \Penoaks\Framework  $fw
	 * @return void
	 */
	public function __construct($fw)
	{
		$this->fw = $fw;
	}

	/**
	 * Get a cache store instance by name.
	 *
	 * @param  string|null  $name
	 * @return mixed
	 */
	public function store($name = null)
	{
		$name = $name ?: $this->getDefaultDriver();

		return $this->stores[$name] = $this->get($name);
	}

	/**
	 * Get a cache driver instance.
	 *
	 * @param  string  $driver
	 * @return mixed
	 */
	public function driver($driver = null)
	{
		return $this->store($driver);
	}

	/**
	 * Attempt to get the store from the local cache.
	 *
	 * @param  string  $name
	 * @return \Penoaks\Contracts\Cache\Repository
	 */
	protected function get($name)
	{
		return isset($this->stores[$name]) ? $this->stores[$name] : $this->resolve($name);
	}

	/**
	 * Resolve the given store.
	 *
	 * @param  string  $name
	 * @return \Penoaks\Contracts\Cache\Repository
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function resolve($name)
	{
		$config = $this->getConfig($name);

		if (is_null($config))
{
			throw new InvalidArgumentException("Cache store [{$name}] is not defined.");
		}

		if (isset($this->customCreators[$config['driver']]))
{
			return $this->callCustomCreator($config);
		}
else
{
			$driverMethod = 'create'.ucfirst($config['driver']).'Driver';

			if (method_exists($this, $driverMethod))
{
				return $this->{$driverMethod}($config);
			}
else
{
				throw new InvalidArgumentException("Driver [{$config['driver']}] is not supported.");
			}
		}
	}

	/**
	 * Call a custom driver creator.
	 *
	 * @param  array  $config
	 * @return mixed
	 */
	protected function callCustomCreator(array $config)
	{
		return $this->customCreators[$config['driver']]($this->fw, $config);
	}

	/**
	 * Create an instance of the APC cache driver.
	 *
	 * @param  array  $config
	 * @return \Penoaks\Cache\ApcStore
	 */
	protected function createApcDriver(array $config)
	{
		$prefix = $this->getPrefix($config);

		return $this->repository(new ApcStore(new ApcWrapper, $prefix));
	}

	/**
	 * Create an instance of the array cache driver.
	 *
	 * @return \Penoaks\Cache\ArrayStore
	 */
	protected function createArrayDriver()
	{
		return $this->repository(new ArrayStore);
	}

	/**
	 * Create an instance of the file cache driver.
	 *
	 * @param  array  $config
	 * @return \Penoaks\Cache\FileStore
	 */
	protected function createFileDriver(array $config)
	{
		return $this->repository(new FileStore($this->fw->bindings['files'], $config['path']));
	}

	/**
	 * Create an instance of the Memcached cache driver.
	 *
	 * @param  array  $config
	 * @return \Penoaks\Cache\MemcachedStore
	 */
	protected function createMemcachedDriver(array $config)
	{
		$prefix = $this->getPrefix($config);

		$memcached = $this->fw->bindings['memcached.connector']->connect($config['servers']);

		return $this->repository(new MemcachedStore($memcached, $prefix));
	}

	/**
	 * Create an instance of the Null cache driver.
	 *
	 * @return \Penoaks\Cache\NullStore
	 */
	protected function createNullDriver()
	{
		return $this->repository(new NullStore);
	}

	/**
	 * Create an instance of the Redis cache driver.
	 *
	 * @param  array  $config
	 * @return \Penoaks\Cache\RedisStore
	 */
	protected function createRedisDriver(array $config)
	{
		$redis = $this->fw->bindings['redis'];

		$connection = Arr::get($config, 'connection', 'default');

		return $this->repository(new RedisStore($redis, $this->getPrefix($config), $connection));
	}

	/**
	 * Create an instance of the database cache driver.
	 *
	 * @param  array  $config
	 * @return \Penoaks\Cache\DatabaseStore
	 */
	protected function createDatabaseDriver(array $config)
	{
		$connection = $this->fw->bindings['db']->connection(Arr::get($config, 'connection'));

		return $this->repository(
			new DatabaseStore(
				$connection, $this->fw->bindings['encrypter'], $config['table'], $this->getPrefix($config)
			)
		);
	}

	/**
	 * Create a new cache repository with the given implementation.
	 *
	 * @param  \Penoaks\Contracts\Cache\Store  $store
	 * @return \Penoaks\Cache\Repository
	 */
	public function repository(Store $store)
	{
		$repository = new Repository($store);

		if ($this->fw->bound('Penoaks\Contracts\Events\Dispatcher'))
{
			$repository->setEventDispatcher(
				$this->fw->bindings['Penoaks\Contracts\Events\Dispatcher']
			);
		}

		return $repository;
	}

	/**
	 * Get the cache prefix.
	 *
	 * @param  array  $config
	 * @return string
	 */
	protected function getPrefix(array $config)
	{
		return Arr::get($config, 'prefix') ?: $this->fw->bindings['config']['cache.prefix'];
	}

	/**
	 * Get the cache connection configuration.
	 *
	 * @param  string  $name
	 * @return array
	 */
	protected function getConfig($name)
	{
		return $this->fw->bindings['config']["cache.stores.{$name}"];
	}

	/**
	 * Get the default cache driver name.
	 *
	 * @return string
	 */
	public function getDefaultDriver()
	{
		return $this->fw->bindings['config']['cache.default'];
	}

	/**
	 * Set the default cache driver name.
	 *
	 * @param  string  $name
	 * @return void
	 */
	public function setDefaultDriver($name)
	{
		$this->fw->bindings['config']['cache.default'] = $name;
	}

	/**
	 * Register a custom driver creator Closure.
	 *
	 * @param  string	$driver
	 * @param  \Closure  $callback
	 * @return $this
	 */
	public function extend($driver, Closure $callback)
	{
		$this->customCreators[$driver] = $callback;

		return $this;
	}

	/**
	 * Dynamically call the default driver instance.
	 *
	 * @param  string  $method
	 * @param  array   $parameters
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		return call_user_func_array([$this->store(), $method], $parameters);
	}
}
