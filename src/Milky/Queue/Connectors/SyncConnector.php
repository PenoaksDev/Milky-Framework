<?php namespace Milky\Queue\Connectors;

use Milky\Queue\SyncQueue;

class SyncConnector implements ConnectorInterface
{
	/**
	 * Establish a queue connection.
	 *
	 * @param  array  $config
	 * @return Queue
	 */
	public function connect(array $config)
	{
		return new SyncQueue;
	}
}
