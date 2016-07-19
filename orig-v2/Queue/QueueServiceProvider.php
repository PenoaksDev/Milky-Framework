<?php
namespace Penoaks\Queue;

use Penoaks\Barebones\ServiceProvider;
use Penoaks\Queue\Connectors\BeanstalkdConnector;
use Penoaks\Queue\Connectors\DatabaseConnector;
use Penoaks\Queue\Connectors\NullConnector;
use Penoaks\Queue\Connectors\RedisConnector;
use Penoaks\Queue\Connectors\SqsConnector;
use Penoaks\Queue\Connectors\SyncConnector;
use Penoaks\Queue\Console\ListenCommand;
use Penoaks\Queue\Console\RestartCommand;
use Penoaks\Queue\Console\WorkCommand;
use Penoaks\Queue\Failed\DatabaseFailedJobProvider;
use Penoaks\Queue\Failed\NullFailedJobProvider;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class QueueServiceProvider extends ServiceProvider
{
	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerManager();

		$this->registerWorker();

		$this->registerListener();

		$this->registerFailedJobServices();

		$this->registerQueueClosure();
	}

	/**
	 * Register the queue manager.
	 *
	 * @return void
	 */
	protected function registerManager()
	{
		$this->bindings->singleton( 'queue', function ( $bindings )
		{
			// Once we have an instance of the queue manager, we will register the various
			// resolvers for the queue connectors. These connectors are responsible for
			// creating the classes that accept queue configs and instantiate queues.
			$manager = new QueueManager( $bindings );

			$this->registerConnectors( $manager );

			return $manager;
		} );

		$this->bindings->singleton( 'queue.connection', function ( $bindings )
		{
			return $bindings['queue']->connection();
		} );
	}

	/**
	 * Register the queue worker.
	 *
	 * @return void
	 */
	protected function registerWorker()
	{
		$this->registerWorkCommand();

		$this->registerRestartCommand();

		$this->bindings->singleton( 'queue.worker', function ( $bindings )
		{
			return new Worker( $bindings['queue'], $bindings['queue.failer'], $bindings['events'] );
		} );
	}

	/**
	 * Register the queue worker console command.
	 *
	 * @return void
	 */
	protected function registerWorkCommand()
	{
		$this->bindings->singleton( 'command.queue.work', function ( $bindings )
		{
			return new WorkCommand( $bindings['queue.worker'] );
		} );

		$this->commands( 'command.queue.work' );
	}

	/**
	 * Register the queue listener.
	 *
	 * @return void
	 */
	protected function registerListener()
	{
		$this->registerListenCommand();

		$this->bindings->singleton( 'queue.listener', function ( $bindings )
		{
			return new Listener( $bindings->basePath() );
		} );
	}

	/**
	 * Register the queue listener console command.
	 *
	 * @return void
	 */
	protected function registerListenCommand()
	{
		$this->bindings->singleton( 'command.queue.listen', function ( $bindings )
		{
			return new ListenCommand( $bindings['queue.listener'] );
		} );

		$this->commands( 'command.queue.listen' );
	}

	/**
	 * Register the queue restart console command.
	 *
	 * @return void
	 */
	public function registerRestartCommand()
	{
		$this->bindings->singleton( 'command.queue.restart', function ()
		{
			return new RestartCommand;
		} );

		$this->commands( 'command.queue.restart' );
	}

	/**
	 * Register the connectors on the queue manager.
	 *
	 * @param  \Penoaks\Queue\QueueManager $manager
	 * @return void
	 */
	public function registerConnectors( $manager )
	{
		foreach ( ['Null', 'Sync', 'Database', 'Beanstalkd', 'Redis', 'Sqs'] as $connector )
		{
			$this->{"register{$connector}Connector"}( $manager );
		}
	}

	/**
	 * Register the Null queue connector.
	 *
	 * @param  \Penoaks\Queue\QueueManager $manager
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
	 * @param  \Penoaks\Queue\QueueManager $manager
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
	 * @param  \Penoaks\Queue\QueueManager $manager
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
	 * @param  \Penoaks\Queue\QueueManager $manager
	 * @return void
	 */
	protected function registerDatabaseConnector( $manager )
	{
		$manager->addConnector( 'database', function ()
		{
			return new DatabaseConnector( $this->bindings['db'] );
		} );
	}

	/**
	 * Register the Redis queue connector.
	 *
	 * @param  \Penoaks\Queue\QueueManager $manager
	 * @return void
	 */
	protected function registerRedisConnector( $manager )
	{
		$bindings = $this->fw;

		$manager->addConnector( 'redis', function () use ( $bindings )
		{
			return new RedisConnector( $bindings['redis'] );
		} );
	}

	/**
	 * Register the Amazon SQS queue connector.
	 *
	 * @param  \Penoaks\Queue\QueueManager $manager
	 * @return void
	 */
	protected function registerSqsConnector( $manager )
	{
		$manager->addConnector( 'sqs', function ()
		{
			return new SqsConnector;
		} );
	}

	/**
	 * Register the failed job services.
	 *
	 * @return void
	 */
	protected function registerFailedJobServices()
	{
		$this->bindings->singleton( 'queue.failer', function ( $bindings )
		{
			$config = $bindings['config']['queue.failed'];

			if ( isset( $config['table'] ) )
			{
				return new DatabaseFailedJobProvider( $bindings['db'], $config['database'], $config['table'] );
			}
			else
			{
				return new NullFailedJobProvider;
			}
		} );
	}

	/**
	 * Register the Illuminate queued closure job.
	 *
	 * @return void
	 */
	protected function registerQueueClosure()
	{
		$this->bindings->singleton( 'IlluminateQueueClosure', function ( $bindings )
		{
			return new IlluminateQueueClosure( $bindings['encrypter'] );
		} );
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [
			'queue',
			'queue.worker',
			'queue.listener',
			'queue.failer',
			'command.queue.work',
			'command.queue.listen',
			'command.queue.restart',
			'queue.connection',
		];
	}
}
