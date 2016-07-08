<?php

namesapce Penoaks\Queue\Connectors;

use Aws\Sqs\SqsClient;
use Foundation\Support\Arr;
use Foundation\Queue\SqsQueue;

class SqsConnector implements ConnectorInterface
{
	/**
	 * Establish a queue connection.
	 *
	 * @param  array  $config
	 * @return \Penoaks\Contracts\Queue\Queue
	 */
	public function connect(array $config)
	{
		$config = $this->getDefaultConfiguration($config);

		if ($config['key'] && $config['secret'])
{
			$config['credentials'] = Arr::only($config, ['key', 'secret']);
		}

		return new SqsQueue(
			new SqsClient($config), $config['queue'], Arr::get($config, 'prefix', '')
		);
	}

	/**
	 * Get the default configuration for SQS.
	 *
	 * @param  array  $config
	 * @return array
	 */
	protected function getDefaultConfiguration(array $config)
	{
		return array_merge([
			'version' => 'latest',
			'http' => [
				'timeout' => 60,
				'connect_timeout' => 60,
			],
		], $config);
	}
}
