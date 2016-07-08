<?php

namesapce Penoaks\Database\Capsule;

use PDO;
use Foundation\Framework;
use Foundation\Database\DatabaseManager;
use Foundation\Contracts\Events\Dispatcher;
use Foundation\Support\Traits\CapsuleManagerTrait;
use Foundation\Database\Eloquent\Model as Eloquent;
use Foundation\Database\Connectors\ConnectionFactory;

class Manager
{
	use CapsuleManagerTrait;

	/**
	 * The database manager instance.
	 *
	 * @var \Penoaks\Database\DatabaseManager
	 */
	protected $manager;

	/**
	 * Create a new database capsule manager.
	 *
	 * @param  \Penoaks\Framework|null  $bindings
	 * @return void
	 */
	public function __construct(Bindings $bindings = null)
	{
		$this->setupBindings($bindings ?: new Bindings);

		// Once we have the bindings setup, we will setup the default configuration
		// options in the bindings "config" binding. This will make the database
		// manager behave correctly since all the correct binding are in place.
		$this->setupDefaultConfiguration();

		$this->setupManager();
	}

	/**
	 * Setup the default database configuration options.
	 *
	 * @return void
	 */
	protected function setupDefaultConfiguration()
	{
		$this->bindings['config']['database.fetch'] = PDO::FETCH_OBJ;

		$this->bindings['config']['database.default'] = 'default';
	}

	/**
	 * Build the database manager instance.
	 *
	 * @return void
	 */
	protected function setupManager()
	{
		$factory = new ConnectionFactory($this->bindings);

		$this->manager = new DatabaseManager($this->bindings, $factory);
	}

	/**
	 * Get a connection instance from the global manager.
	 *
	 * @param  string  $connection
	 * @return \Penoaks\Database\Connection
	 */
	public static function connection($connection = null)
	{
		return static::$instance->getConnection($connection);
	}

	/**
	 * Get a fluent query builder instance.
	 *
	 * @param  string  $table
	 * @param  string  $connection
	 * @return \Penoaks\Database\Query\Builder
	 */
	public static function table($table, $connection = null)
	{
		return static::$instance->connection($connection)->table($table);
	}

	/**
	 * Get a schema builder instance.
	 *
	 * @param  string  $connection
	 * @return \Penoaks\Database\Schema\Builder
	 */
	public static function schema($connection = null)
	{
		return static::$instance->connection($connection)->getSchemaBuilder();
	}

	/**
	 * Get a registered connection instance.
	 *
	 * @param  string  $name
	 * @return \Penoaks\Database\Connection
	 */
	public function getConnection($name = null)
	{
		return $this->manager->connection($name);
	}

	/**
	 * Register a connection with the manager.
	 *
	 * @param  array   $config
	 * @param  string  $name
	 * @return void
	 */
	public function addConnection(array $config, $name = 'default')
	{
		$connections = $this->bindings['config']['database.connections'];

		$connections[$name] = $config;

		$this->bindings['config']['database.connections'] = $connections;
	}

	/**
	 * Bootstrap Eloquent so it is ready for usage.
	 *
	 * @return void
	 */
	public function bootEloquent()
	{
		Eloquent::setConnectionResolver($this->manager);

		// If we have an event dispatcher instance, we will go ahead and register it
		// with the Eloquent ORM, allowing for model callbacks while creating and
		// updating "model" instances; however, if it not necessary to operate.
		if ($dispatcher = $this->getEventDispatcher())
{
			Eloquent::setEventDispatcher($dispatcher);
		}
	}

	/**
	 * Set the fetch mode for the database connections.
	 *
	 * @param  int  $fetchMode
	 * @return $this
	 */
	public function setFetchMode($fetchMode)
	{
		$this->bindings['config']['database.fetch'] = $fetchMode;

		return $this;
	}

	/**
	 * Get the database manager instance.
	 *
	 * @return \Penoaks\Database\DatabaseManager
	 */
	public function getDatabaseManager()
	{
		return $this->manager;
	}

	/**
	 * Get the current event dispatcher instance.
	 *
	 * @return \Penoaks\Contracts\Events\Dispatcher|null
	 */
	public function getEventDispatcher()
	{
		if ($this->bindings->bound('events'))
{
			return $this->bindings['events'];
		}
	}

	/**
	 * Set the event dispatcher instance to be used by connections.
	 *
	 * @param  \Penoaks\Contracts\Events\Dispatcher  $dispatcher
	 * @return void
	 */
	public function setEventDispatcher(Dispatcher $dispatcher)
	{
		$this->bindings->instance('events', $dispatcher);
	}

	/**
	 * Dynamically pass methods to the default connection.
	 *
	 * @param  string  $method
	 * @param  array   $parameters
	 * @return mixed
	 */
	public static function __callStatic($method, $parameters)
	{
		return call_user_func_array([static::connection(), $method], $parameters);
	}
}
