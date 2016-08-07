<?php namespace Milky\Queue\Connectors;

use Pheanstalk\Pheanstalk;
use Milky\Helpers\Arr;
use Pheanstalk\PheanstalkInterface;
use Milky\Queue\BeanstalkdQueue;

class BeanstalkdConnector implements ConnectorInterface
{
	/**
	 * Establish a queue connection.
	 *
	 * @param  array  $config
	 * @return Queue
	 */
	public function connect(array $config)
	{
		$pheanstalk = new Pheanstalk($config['host'], Arr::get($config, 'port', PheanstalkInterface::DEFAULT_PORT));

		return new BeanstalkdQueue(
			$pheanstalk, $config['queue'], Arr::get($config, 'ttr', Pheanstalk::DEFAULT_TTR)
		);
	}
}
