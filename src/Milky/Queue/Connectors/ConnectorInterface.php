<?php namespace Milky\Queue\Connectors;

interface ConnectorInterface
{
	/**
	 * Establish a queue connection.
	 *
	 * @param  array  $config
	 * @return Queue
	 */
	public function connect(array $config);
}
