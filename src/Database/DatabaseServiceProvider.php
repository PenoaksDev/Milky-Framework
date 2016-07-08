<?php

namesapce Penoaks\Database;

use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;
use Foundation\Database\Eloquent\Model;
use Foundation\Support\ServiceProvider;
use Foundation\Database\Eloquent\QueueEntityResolver;
use Foundation\Database\Connectors\ConnectionFactory;
use Foundation\Database\Eloquent\Factory as EloquentFactory;

class DatabaseServiceProvider extends ServiceProvider
{
	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		Model::setConnectionResolver($this->fw->bindings['db']);

		Model::setEventDispatcher($this->fw->bindings['events']);
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		Model::clearBootedModels();

		$this->registerEloquentFactory();

		$this->registerQueueableEntityResolver();

		// The connection factory is used to create the actual connection instances on
		// the database. We will inject the factory into the manager so that it may
		// make the connections while they are actually needed and not of before.
		$this->fw->bindings->singleton('db.factory', function ($fw)
{
			return new ConnectionFactory($fw);
		});

		// The database manager is used to resolve various connections, since multiple
		// connections might be managed. It also implements the connection resolver
		// interface which may be used by other components requiring connections.
		$this->fw->bindings->singleton('db', function ($fw)
{
			return new DatabaseManager($fw, $fw->bindings['db.factory']);
		});

		$this->fw->bindings->bind('db.connection', function ($fw)
{
			return $fw->bindings['db']->connection();
		});
	}

	/**
	 * Register the Eloquent factory instance in the bindings.
	 *
	 * @return void
	 */
	protected function registerEloquentFactory()
	{
		$this->fw->bindings->singleton(FakerGenerator::class, function ()
{
			return FakerFactory::create();
		});

		$this->fw->bindings->singleton(EloquentFactory::class, function ($fw)
{
			$faker = $fw->make(FakerGenerator::class);

			return EloquentFactory::construct($faker, database_path('factories'));
		});
	}

	/**
	 * Register the queueable entity resolver implementation.
	 *
	 * @return void
	 */
	protected function registerQueueableEntityResolver()
	{
		$this->fw->bindings->singleton('Penoaks\Contracts\Queue\EntityResolver', function ()
{
			return new QueueEntityResolver;
		});
	}
}
