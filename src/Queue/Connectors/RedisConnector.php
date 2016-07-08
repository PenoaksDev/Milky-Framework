<?php

namesapce Penoaks\Queue\Connectors;

use Foundation\Support\Arr;
use Foundation\Redis\Database;
use Foundation\Queue\RedisQueue;

class RedisConnector implements ConnectorInterface
{
	/**
	 * The Redis database instance.
	 *
	 * @var \Penoaks\Redis\Database
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
	 * @param  \Penoaks\Redis\Database  $redis
	 * @param  string|null  $connection
	 * @return void
	 */
	public function __construct(Database $redis, $connection = null)
	{
		$this->redis = $redis;
		$this->connection = $connection;
	}

	/**
	 * Establish a queue connection.
	 *
	 * @param  array  $config
	 * @return \Penoaks\Contracts\Queue\Queue
	 */
	public function connect(array $config)
	{
		$queue = new RedisQueue(
			$this->redis, $config['queue'], Arr::get($config, 'connection', $this->connection)
		);

		$queue->setExpire(Arr::get($config, 'expire', 60));

		return $queue;
	}
}
