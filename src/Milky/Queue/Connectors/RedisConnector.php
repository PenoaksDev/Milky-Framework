<?php namespace Milky\Queue\Connectors;

use Milky\Helpers\Arr;
use Milky\Queue\RedisQueue;
use Milky\Redis\Redis;

class RedisConnector implements ConnectorInterface
{
	/**
	 * The Redis database instance.
	 *
	 * @var Redis
	 */
	protected $redis;

	/**
	 * The connection name.
	 *
	 * @var string
	 */
	protected $connection;

	/**
	 * Create a new Redis queue connector instance.
	 *
	 * @param  Redis $redis
	 * @param  string|null $connection
	 * @return void
	 */
	public function __construct( Redis $redis, $connection = null )
	{
		$this->redis = $redis;
		$this->connection = $connection;
	}

	/**
	 * Establish a queue connection.
	 *
	 * @param  array $config
	 * @return Queue
	 */
	public function connect( array $config )
	{
		$queue = new RedisQueue( $this->redis, $config['queue'], Arr::get( $config, 'connection', $this->connection ) );

		$queue->setExpire( Arr::get( $config, 'expire', 60 ) );

		return $queue;
	}
}
