<?php namespace Milky\Queue;

use Closure;
use InvalidArgumentException;
use Milky\Framework;
use Milky\Queue\Connectors\ConnectorInterface;

class QueueManager
{
	/**
	 * The array of resolved queue connections.
	 *
	 * @var array
	 */
	protected $connections = [];

	/**
	 * The array of resolved queue connectors.
	 *
	 * @var array
	 */
	protected $connectors = [];

	/**
	 * Register an event listener for the before job event.
	 *
	 * @param  mixed $callback
	 */
	public function before( $callback )
	{
		Framework::hooks()->addHook( 'queue.job.processing', $callback );
	}

	/**
	 * Register an event listener for the after job event.
	 *
	 * @param  mixed $callback
	 */
	public function after( $callback )
	{
		Framework::hooks()->addHook( 'queue.job.processed', $callback );
	}

	/**
	 * Register an event listener for the exception occurred job event.
	 *
	 * @param  mixed $callback
	 */
	public function exceptionOccurred( $callback )
	{
		Framework::hooks()->addHook( 'queue.job.exception', $callback );
	}

	/**
	 * Register an event listener for the daemon queue loop.
	 *
	 * @param  mixed $callback
	 */
	public function looping( $callback )
	{
		Framework::hooks()->addHook( 'queue.looping', $callback );
	}

	/**
	 * Register an event listener for the failed job event.
	 *
	 * @param  mixed $callback
	 */
	public function failing( $callback )
	{
		Framework::hooks()->addHook( 'queue.job.failed', $callback );
	}

	/**
	 * Register an event listener for the daemon queue stopping.
	 *
	 * @param  mixed $callback
	 */
	public function stopping( $callback )
	{
		Framework::hooks()->addHook( 'queue.worker.stopping', $callback );
	}

	/**
	 * Determine if the driver is connected.
	 *
	 * @param  string $name
	 * @return bool
	 */
	public function connected( $name = null )
	{
		return isset( $this->connections[$name ?: $this->getDefaultDriver()] );
	}

	/**
	 * Resolve a queue connection instance.
	 *
	 * @param  string $name
	 * @return Queue
	 */
	public function connection( $name = null )
	{
		$name = $name ?: $this->getDefaultDriver();

		// If the connection has not been resolved yet we will resolve it now as all
		// of the connections are resolved when they are actually needed so we do
		// not make any unnecessary connection to the various queue end-points.
		if ( !isset( $this->connections[$name] ) )
		{
			$this->connections[$name] = $this->resolve( $name );

			$this->connections[$name]->setEncrypter( Framework::get( 'encrypter' ) );
		}

		return $this->connections[$name];
	}

	/**
	 * Resolve a queue connection.
	 *
	 * @param  string $name
	 * @return Queue
	 */
	protected function resolve( $name )
	{
		$config = $this->getConfig( $name );

		return $this->getConnector( $config['driver'] )->connect( $config );
	}

	/**
	 * Get the connector for a given driver.
	 *
	 * @param  string $driver
	 * @return ConnectorInterface
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function getConnector( $driver )
	{
		if ( isset( $this->connectors[$driver] ) )
		{
			return call_user_func( $this->connectors[$driver] );
		}

		throw new InvalidArgumentException( "No connector for [$driver]" );
	}

	/**
	 * Add a queue connection resolver.
	 *
	 * @param  string $driver
	 * @param  \Closure $resolver
	 */
	public function extend( $driver, Closure $resolver )
	{
		$this->addConnector( $driver, $resolver );
	}

	/**
	 * Add a queue connection resolver.
	 *
	 * @param  string $driver
	 * @param  \Closure $resolver
	 */
	public function addConnector( $driver, Closure $resolver )
	{
		$this->connectors[$driver] = $resolver;
	}

	/**
	 * Get the queue connection configuration.
	 *
	 * @param  string $name
	 * @return array
	 */
	protected function getConfig( $name )
	{
		if ( $name === null || $name === 'null' )
			return ['driver' => 'null'];

		return Framework::config()->get( 'queue.connections.' . $name );
	}

	/**
	 * Get the name of the default queue connection.
	 *
	 * @return string
	 */
	public function getDefaultDriver()
	{
		return Framework::config()->get( 'queue.default' );
	}

	/**
	 * Set the name of the default queue connection.
	 *
	 * @param  string $name
	 */
	public function setDefaultDriver( $name )
	{
		Framework::config()->set( 'queue.default', $name );
	}

	/**
	 * Get the full name for the given connection.
	 *
	 * @param  string $connection
	 * @return string
	 */
	public function getName( $connection = null )
	{
		return $connection ?: $this->getDefaultDriver();
	}

	/**
	 * Determine if the application is in maintenance mode.
	 *
	 * @return bool
	 */
	public function isDownForMaintenance()
	{
		return Framework::fw()->isDownForMaintenance();
	}

	/**
	 * Dynamically pass calls to the default connection.
	 *
	 * @param  string $method
	 * @param  array $parameters
	 * @return mixed
	 */
	public function __call( $method, $parameters )
	{
		$callable = [$this->connection(), $method];

		return call_user_func_array( $callable, $parameters );
	}
}
