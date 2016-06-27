<?php

namespace Foundation\Queue\Connectors;

use Pheanstalk\Pheanstalk;
use Foundation\Support\Arr;
use Pheanstalk\PheanstalkInterface;
use Foundation\Queue\BeanstalkdQueue;

class BeanstalkdConnector implements ConnectorInterface
{
	/**
	 * Establish a queue connection.
	 *
	 * @param  array  $config
	 * @return \Foundation\Contracts\Queue\Queue
	 */
	public function connect(array $config)
	{
		$pheanstalk = new Pheanstalk($config['host'], Arr::get($config, 'port', PheanstalkInterface::DEFAULT_PORT));

		return new BeanstalkdQueue(
			$pheanstalk, $config['queue'], Arr::get($config, 'ttr', Pheanstalk::DEFAULT_TTR)
		);
	}
}
