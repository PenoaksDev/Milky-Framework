<?php namespace Milky\Queue;

use Milky\Framework;
use Milky\Providers\ServiceProvider;
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
		$manager = new QueueManager();
		Framework::set( 'queue.mgr', $manager );

		foreach ( ['Null', 'Sync', 'Database', 'Beanstalkd', 'Redis', 'Sqs'] as $connector )
			$this->{"register{$connector}Connector"}( $manager );

		Framework::set( 'queue.connection', $manager->connection() );

		$listener = new Listener( Framework::fw()->basePath );
		Framework::set( 'queue.listener', $listener );

		$config = Framework::config()->get( 'queue.failed' );

		if ( isset( $config['table'] ) )
			Framework::set( 'queue.failer', $failer = Framework::set( 'queue.failer', new DatabaseFailedJobProvider( Framework::get( 'db' ), $config['database'], $config['table'] ) ) );
		else
			Framework::set( 'queue.failer', $failer = Framework::set( 'queue.failer', new NullFailedJobProvider ) );

		Framework::set( 'queue.worker', $worker = new Worker( $manager, $failer ) );

		Framework::set( 'command.queue.work', new WorkCommand( $worker ) );
		$this->commands( 'command.queue.work' );

		Framework::set( 'command.queue.restart', new RestartCommand );
		$this->commands( 'command.queue.restart' );

		Framework::set( 'command.queue.listen', new ListenCommand( $listener ) );
		$this->commands( 'command.queue.listen' );

		Framework::set( 'Milky\Queue\Closure', new IlluminateQueueClosure( Framework::get( 'encrypter' ) ) );
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
