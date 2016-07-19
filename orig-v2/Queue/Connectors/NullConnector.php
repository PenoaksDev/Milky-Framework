<?php

namespace Penoaks\Queue\Connectors;

use Penoaks\Queue\NullQueue;

class NullConnector implements ConnectorInterface
{
	/**
	 * Establish a queue connection.
	 *
	 * @param  array  $config
	 * @return \Penoaks\Contracts\Queue\Queue
	 */
	public function connect(array $config)
	{
		return new NullQueue;
	}
}
