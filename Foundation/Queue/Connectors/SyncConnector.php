<?php

namespace Foundation\Queue\Connectors;

use Foundation\Queue\SyncQueue;

class SyncConnector implements ConnectorInterface
{
	/**
	 * Establish a queue connection.
	 *
	 * @param  array  $config
	 * @return \Foundation\Contracts\Queue\Queue
	 */
	public function connect(array $config)
	{
		return new SyncQueue;
	}
}
