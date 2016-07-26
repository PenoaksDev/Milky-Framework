<?php namespace Milky\Queue\Connectors;

use Milky\Queue\NullQueue;

class NullConnector implements ConnectorInterface
{
	/**
	 * Establish a queue connection.
	 *
	 * @param  array  $config
	 * @return Queue
	 */
	public function connect(array $config)
	{
		return new NullQueue;
	}
}
