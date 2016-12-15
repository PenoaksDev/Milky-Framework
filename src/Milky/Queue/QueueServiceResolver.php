<?php namespace Milky\Queue;

use Milky\Binding\ServiceResolver;
use Milky\Binding\UniversalBuilder;
use Milky\Database\DatabaseManager;
use Milky\Facades\Config;
use Milky\Framework;
use Milky\Queue\Connectors\BeanstalkdConnector;
use Milky\Queue\Connectors\DatabaseConnector;
use Milky\Queue\Connectors\NullConnector;
use Milky\Queue\Connectors\RedisConnector;
use Milky\Queue\Connectors\SqsConnector;
use Milky\Queue\Connectors\SyncConnector;
use Milky\Queue\Console\ListenCommand;
use Milky\Queue\Console\RestartCommand;
use Milky\Queue\Console\WorkCommand;
use Milky\Queue\Failed\DatabaseFailedJobProvider;
use Milky\Queue\Failed\NullFailedJobProvider;

/**
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class QueueServiceResolver extends ServiceResolver
{
	protected $mgr;
	protected $connection;
	protected $listener;
	protected $failer;
	protected $worker;

	public function __construct()
	{
		$this->mgr = new QueueManager();

		foreach ( ['Null', 'Sync', 'Database', 'Beanstalkd', 'Redis', 'Sqs'] as $connector )
			$this->{"register{$connector}Connector"}( $this->mgr );

		$this->connection = $this->mgr->connection();

		$this->listener = new Listener( Framework::fw()->basePath );

		$config = Config::get( 'queue.failed' );

		if ( isset( $config['table'] ) )
			$this->failer = new DatabaseFailedJobProvider( UniversalBuilder::resolveClass( DatabaseManager::class ), $config['database'], $config['table'] );
		else
			$this->failer = new NullFailedJobProvider;

		$this->worker = new Worker( $this->mgr, $this->failer );

		UniversalBuilder::getResolver( 'command' )->queueWork = new WorkCommand( $this->worker );
		UniversalBuilder::getResolver( 'command' )->queueRestart = new RestartCommand( $this->worker );
		UniversalBuilder::getResolver( 'command' )->queueListen = new ListenCommand( $this->listener );

		// Framework::set( 'Milky\Queue\Closure', new QueueClosure( Framework::get( 'encrypter' ) ) ); WHAT IS THIS?
	}

	/**
	 * Register the Null queue connector.
	 *
	 * @param  QueueManager $manager
	 * @return void
	 */
	protected function registerNullConnector( $manager )
	{
		$manager->addConnector( 'null', function ()
		{
			return new NullConnector;
		} );
	}

	/**
	 * Register the Sync queue connector.
	 *
	 * @param  QueueManager $manager
	 * @return void
	 */
	protected function registerSyncConnector( $manager )
	{
		$manager->addConnector( 'sync', function ()
		{
			return new SyncConnector;
		} );
	}

	/**
	 * Register the Beanstalkd queue connector.
	 *
	 * @param  QueueManager $manager
	 * @return void
	 */
	protected function registerBeanstalkdConnector( $manager )
	{
		$manager->addConnector( 'beanstalkd', function ()
		{
			return new BeanstalkdConnector;
		} );
	}

	/**
	 * Register the database queue connector.
	 *
	 * @param  QueueManager $manager
	 * @return void
	 */
	protected function registerDatabaseConnector( $manager )
	{
		$manager->addConnector( 'database', function ()
		{
			return new DatabaseConnector( Framework::get( 'db' ) );
		} );
	}

	/**
	 * Register the Redis queue connector.
	 *
	 * @param  QueueManager $manager
	 * @return void
	 */
	protected function registerRedisConnector( $manager )
	{
		$manager->addConnector( 'redis', function ()
		{
			return new RedisConnector( Framework::get( 'redis' ) );
		} );
	}

	/**
	 * Register the Amazon SQS queue connector.
	 *
	 * @param  QueueManager $manager
	 * @return void
	 */
	protected function registerSqsConnector( $manager )
	{
		$manager->addConnector( 'sqs', function ()
		{
			return new SqsConnector;
		} );
	}

	public function key()
	{
		return 'queue';
	}
}
