<?php

namespace Penoaks\Queue;

use IlluminateQueueClosure;
use Penoaks\Support\ServiceProvider;
use Penoaks\Queue\Console\WorkCommand;
use Penoaks\Queue\Console\ListenCommand;
use Penoaks\Queue\Console\RestartCommand;
use Penoaks\Queue\Connectors\SqsConnector;
use Penoaks\Queue\Connectors\NullConnector;
use Penoaks\Queue\Connectors\SyncConnector;
use Penoaks\Queue\Connectors\RedisConnector;
use Penoaks\Queue\Failed\NullFailedJobProvider;
use Penoaks\Queue\Connectors\DatabaseConnector;
use Penoaks\Queue\Connectors\BeanstalkdConnector;
use Penoaks\Queue\Failed\DatabaseFailedJobProvider;

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
		$this->fw->bindings->singleton('queue', function ($fw)
{
			// Once we have an instance of the queue manager, we will register the various
			// resolvers for the queue connectors. These connectors are responsible for
			// creating the classes that accept queue configs and instantiate queues.
			$manager = new QueueManager($fw);

			$this->registerConnectors($manager);

			return $manager;
		});

		$this->fw->bindings->singleton('queue.connection', function ($fw)
{
			return $fw->bindings['queue']->connection();
		});
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

		$this->fw->bindings->singleton('queue.worker', function ($fw)
{
			return new Worker($fw->bindings['queue'], $fw->bindings['queue.failer'], $fw->bindings['events']);
		});
	}

	/**
	 * Register the queue worker console command.
	 *
	 * @return void
	 */
	protected function registerWorkCommand()
	{
		$this->fw->bindings->singleton('command.queue.work', function ($fw)
{
			return new WorkCommand($fw->bindings['queue.worker']);
		});

		$this->commands('command.queue.work');
	}

	/**
	 * Register the queue listener.
	 *
	 * @return void
	 */
	protected function registerListener()
	{
		$this->registerListenCommand();

		$this->fw->bindings->singleton('queue.listener', function ($fw)
{
			return new Listener($fw->basePath());
		});
	}

	/**
	 * Register the queue listener console command.
	 *
	 * @return void
	 */
	protected function registerListenCommand()
	{
		$this->fw->bindings->singleton('command.queue.listen', function ($fw)
{
			return new ListenCommand($fw->bindings['queue.listener']);
		});

		$this->commands('command.queue.listen');
	}

	/**
	 * Register the queue restart console command.
	 *
	 * @return void
	 */
	public function registerRestartCommand()
	{
		$this->fw->bindings->singleton('command.queue.restart', function ()
{
			return new RestartCommand;
		});

		$this->commands('command.queue.restart');
	}

	/**
	 * Register the connectors on the queue manager.
	 *
	 * @param  \Penoaks\Queue\QueueManager  $manager
	 * @return void
	 */
	public function registerConnectors($manager)
	{
		foreach (['Null', 'Sync', 'Database', 'Beanstalkd', 'Redis', 'Sqs'] as $connector)
{
			$this->{"register{$connector}Connector"}($manager);
		}
	}

	/**
	 * Register the Null queue connector.
	 *
	 * @param  \Penoaks\Queue\QueueManager  $manager
	 * @return void
	 */
	protected function registerNullConnector($manager)
	{
		$manager->addConnector('null', function ()
{
			return new NullConnector;
		});
	}

	/**
	 * Register the Sync queue connector.
	 *
	 * @param  \Penoaks\Queue\QueueManager  $manager
	 * @return void
	 */
	protected function registerSyncConnector($manager)
	{
		$manager->addConnector('sync', function ()
{
			return new SyncConnector;
		});
	}

	/**
	 * Register the Beanstalkd queue connector.
	 *
	 * @param  \Penoaks\Queue\QueueManager  $manager
	 * @return void
	 */
	protected function registerBeanstalkdConnector($manager)
	{
		$manager->addConnector('beanstalkd', function ()
{
			return new BeanstalkdConnector;
		});
	}

	/**
	 * Register the database queue connector.
	 *
	 * @param  \Penoaks\Queue\QueueManager  $manager
	 * @return void
	 */
	protected function registerDatabaseConnector($manager)
	{
		$manager->addConnector('database', function ()
{
			return new DatabaseConnector($this->fw->bindings['db']);
		});
	}

	/**
	 * Register the Redis queue connector.
	 *
	 * @param  \Penoaks\Queue\QueueManager  $manager
	 * @return void
	 */
	protected function registerRedisConnector($manager)
	{
		$fw = $this->fw;

		$manager->addConnector('redis', function () use ($fw)
{
			return new RedisConnector($fw->bindings['redis']);
		});
	}

	/**
	 * Register the Amazon SQS queue connector.
	 *
	 * @param  \Penoaks\Queue\QueueManager  $manager
	 * @return void
	 */
	protected function registerSqsConnector($manager)
	{
		$manager->addConnector('sqs', function ()
{
			return new SqsConnector;
		});
	}

	/**
	 * Register the failed job services.
	 *
	 * @return void
	 */
	protected function registerFailedJobServices()
	{
		$this->fw->bindings->singleton('queue.failer', function ($fw)
{
			$config = $fw->bindings['config']['queue.failed'];

			if (isset($config['table']))
{
				return new DatabaseFailedJobProvider($fw->bindings['db'], $config['database'], $config['table']);
			}
else
{
				return new NullFailedJobProvider;
			}
		});
	}

	/**
	 * Register the Illuminate queued closure job.
	 *
	 * @return void
	 */
	protected function registerQueueClosure()
	{
		$this->fw->bindings->singleton('IlluminateQueueClosure', function ($fw)
{
			return new IlluminateQueueClosure($fw->bindings['encrypter']);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [
			'queue', 'queue.worker', 'queue.listener', 'queue.failer',
			'command.queue.work', 'command.queue.listen',
			'command.queue.restart', 'queue.connection',
		];
	}
}
