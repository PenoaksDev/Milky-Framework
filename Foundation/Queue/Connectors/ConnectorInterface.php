<?php

namespace Foundation\Queue\Connectors;

interface ConnectorInterface
{
	/**
	 * Establish a queue connection.
	 *
	 * @param  array  $config
	 * @return \Foundation\Contracts\Queue\Queue
	 */
	public function connect(array $config);
}
