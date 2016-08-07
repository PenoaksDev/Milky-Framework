<?php namespace Milky\Cache;

use Closure;
use InvalidArgumentException;
use Milky\Annotations\Cache;
use Milky\Binding\UniversalBuilder;
use Milky\Database\DatabaseManager;
use Milky\Encryption\Encrypter;
use Milky\Facades\Config;
use Milky\Filesystem\Filesystem;
use Milky\Framework;
use Milky\Helpers\Arr;

class CacheManager implements Cache
{
	/**
	 * The array of resolved cache stores.
	 *
	 * @var Store[]
	 */
	protected $stores = [];

	/**
	 * The registered custom driver creators.
	 *
	 * @var array
	 */
	protected $customCreators = [];

	public static function i()
	{
		return UniversalBuilder::resolveClass( static::class );
	}

	/**
	 * Get a cache store instance by name.
	 *
	 * @param  string|null $name
	 * @return Store
	 */
	public function store( $name = null )
	{
		$name = $name ?: $this->getDefaultDriver();

		return $this->stores[$name] = $this->get( $name );
	}

	/**
	 * Attempt to get the store from the local cache.
	 *
	 * @param  string $name
	 * @return Store
	 */
	protected function get( $name )
	{
		return isset( $this->stores[$name] ) ? $this->stores[$name] : $this->resolve( $name );
	}

	/**
	 * Resolve the given store.
	 *
	 * @param  string $name
	 * @return Store
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function resolve( $name )
	{
		$config = $this->getConfig( $name );

		if ( is_null( $config ) )
			throw new InvalidArgumentException( "Cache store [{$name}] is not defined." );

		if ( isset( $this->customCreators[$config['driver']] ) )
			return $this->callCustomCreator( $config );
		else
		{
			$driverMethod = 'create' . ucfirst( $config['driver'] ) . 'Driver';

			if ( method_exists( $this, $driverMethod ) )
				return $this->{$driverMethod}( $config );
			else
				throw new InvalidArgumentException( "Driver [{$config['driver']}] is not supported." );
		}
	}

	/**
	 * Call a custom driver creator.
	 *
	 * @param  array $config
	 * @return mixed
	 */
	protected function callCustomCreator( array $config )
	{
		return $this->customCreators[$config['driver']]( $config );
	}

	/**
	 * Create an instance of the APC cache driver.
	 *
	 * @param  array $config
	 * @return Store
	 */
	protected function createApcDriver( array $config )
	{
		$prefix = $this->getPrefix( $config );

		return new ApcStore( new ApcWrapper, $prefix );
	}

	/**
	 * Create an instance of the array cache driver.
	 *
	 * @return Store
	 */
	protected function createArrayDriver()
	{
		return new ArrayStore;
	}

	/**
	 * Create an instance of the file cache driver.
	 *
	 * @param  array $config
	 * @return Store
	 */
	protected function createFileDriver( array $config )
	{
		return new FileStore( Filesystem::i(), $config['path'] );
	}

	/**
	 * Create an instance of the Memcached cache driver.
	 *
	 * @param  array $config
	 * @return Store
	 */
	protected function createMemcachedDriver( array $config )
	{
		$prefix = $this->getPrefix( $config );

		$memcached = MemcachedConnector::i()->connect( $config['servers'] );

		return new MemcachedStore( $memcached, $prefix );
	}

	/**
	 * Create an instance of the Null cache driver.
	 *
	 * @return Store
	 */
	protected function createNullDriver()
	{
		return new NullStore;
	}

	/**
	 * Create an instance of the Redis cache driver.
	 *
	 * @param  array $config
	 * @return Store
	 */
	protected function createRedisDriver( array $config )
	{
		$redis = Framework::get( 'redis' );

		$connection = Arr::get( $config, 'connection', 'default' );

		return new RedisStore( $redis, $this->getPrefix( $config ), $connection );
	}

	/**
	 * Create an instance of the database cache driver.
	 *
	 * @param  array $config
	 * @return Store
	 */
	protected function createDatabaseDriver( array $config )
	{
		return new DatabaseStore( DatabaseManager::i()->connection( Arr::get( $config, 'connection' ) ), Encrypter::i(), $config['table'], $this->getPrefix( $config ) );
	}

	/**
	 * Create a new cache repository with the given implementation.
	 *
	 * @param  Store $store
	 * @return Repository
	 */
	public function repository( Store $store = null )
	{
		return new Repository( $store ?: $this->store() );
	}

	/**
	 * Get the cache prefix.
	 *
	 * @param  array $config
	 * @return string
	 */
	protected function getPrefix( array $config )
	{
		return Arr::get( $config, 'prefix' ) ?: Config::get( 'cache.prefix' );
	}

	/**
	 * Get the cache connection configuration.
	 *
	 * @param  string $name
	 * @return array
	 */
	protected function getConfig( $name )
	{
		return Config::get( 'cache.stores.' . $name );
	}

	/**
	 * Get the default cache driver name.
	 *
	 * @return string
	 */
	public function getDefaultDriver()
	{
		return Config::get( 'cache.default' );
	}

	/**
	 * Set the default cache driver name.
	 *
	 * @param  string $name
	 */
	public function setDefaultDriver( $name )
	{
		Config::set( 'cache.default', $name );
	}

	/**
	 * Register a custom driver creator Closure.
	 *
	 * @param  string $driver
	 * @param  \Closure $callback
	 * @return $this
	 */
	public function extend( $driver, Closure $callback )
	{
		$this->customCreators[$driver] = $callback;

		return $this;
	}

	public function fetch( $id )
	{
		return $this->store()->get( $id ) ?: false;
	}

	public function contains( $id )
	{
		return !is_null( $this->store()->get( $id ) );
	}

	public function save( $id, $data, $lifeTime = 0 )
	{
		$this->store()->put( $id, $data, $lifeTime );

		return true;
	}

	public function delete( $id )
	{
		$this->store()->forget( $id );

		return true;
	}

	/**
	 * Retrieves cached information from the data store.
	 *
	 * The server's statistics array has the following values:
	 *
	 * - <b>hits</b>
	 * Number of keys that have been requested and found present.
	 *
	 * - <b>misses</b>
	 * Number of items that have been requested and not found.
	 *
	 * - <b>uptime</b>
	 * Time that the server is running.
	 *
	 * - <b>memory_usage</b>
	 * Memory used by this server to store items.
	 *
	 * - <b>memory_available</b>
	 * Memory allowed to use for storage.
	 *
	 * @since 2.2
	 *
	 * @return array|null An associative array with server's statistics if available, NULL otherwise.
	 */
	public function getStats()
	{
		return null; // FOR NOW
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
		$repo = $this->repository( $this->store() );

		if ( !method_exists( $repo, $method ) )
			throw new InvalidArgumentException( "The method [" . $method . "] in CacheManager or Repository does not exist." );

		return call_user_func_array( [$repo, $method], $parameters );
	}
}
