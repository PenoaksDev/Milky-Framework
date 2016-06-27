<?php

namespace Foundation\Queue\Connectors;

use Foundation\Support\Arr;
use Foundation\Queue\DatabaseQueue;
use Foundation\Database\ConnectionResolverInterface;

class DatabaseConnector implements ConnectorInterface
{
	/**
	 * Database connections.
	 *
	 * @var \Foundation\Database\ConnectionResolverInterface
	 */
	protected $connections;

	/**
	 * Create a new connector instance.
	 *
	 * @param  \Foundation\Database\ConnectionResolverInterface  $connections
	 * @return void
	 */
	public function __construct(ConnectionResolverInterface $connections)
	{
		$this->connections = $connections;
	}

	/**
	 * Establish a queue connection.
	 *
	 * @param  array  $config
	 * @return \Foundation\Contracts\Queue\Queue
	 */
	public function connect(array $config)
	{
		return new DatabaseQueue(
			$this->connections->connection(Arr::get($config, 'connection')),
			$config['table'],
			$config['queue'],
			Arr::get($config, 'expire', 60)
		);
	}
}
