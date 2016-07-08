<?php

namespace Penoaks\Queue\Connectors;

use Penoaks\Queue\SyncQueue;

class SyncConnector implements ConnectorInterface
{
	/**
	 * Establish a queue connection.
	 *
	 * @param  array  $config
	 * @return \Penoaks\Contracts\Queue\Queue
	 */
	public function connect(array $config)
	{
		return new SyncQueue;
	}
}
