<?php

namespace Penoaks\Queue\Connectors;

use Penoaks\Support\Arr;
use Penoaks\Queue\DatabaseQueue;
use Penoaks\Database\ConnectionResolverInterface;

class DatabaseConnector implements ConnectorInterface
{
	/**
	 * Database connections.
	 *
	 * @var \Penoaks\Database\ConnectionResolverInterface
	 */
	protected $connections;

	/**
	 * Create a new connector instance.
	 *
	 * @param  \Penoaks\Database\ConnectionResolverInterface  $connections
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
	 * @return \Penoaks\Contracts\Queue\Queue
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
