<?php namespace Milky\Providers;

use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;
use Milky\Binding\BindingBuilder;
use Milky\Database\Connectors\ConnectionFactory;
use Milky\Database\DatabaseManager;
use Milky\Database\Eloquent\Factory as EloquentFactory;
use Milky\Database\Eloquent\Model;
use Milky\Database\Eloquent\QueueEntityResolver;
use Milky\Framework;
use Penoaks\Contracts\Queue\EntityResolver;

class DatabaseServiceProvider extends ServiceProvider
{
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

		$fw = Framework::fw();

		$factory = new ConnectionFactory();
		$db = new DatabaseManager( $factory );

		$fw['db.mgr'] = $db;
		$fw['db.factory'] = $factory;
		$fw['db.connection'] = $db->connection();
	}

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		Model::setConnectionResolver( Framework::get( 'db.mgr' ) );
	}

	/**
	 * Register the Eloquent factory instance in the container.
	 *
	 * @return void
	 */
	protected function registerEloquentFactory()
	{
		BindingBuilder::addServiceBindingResolver( FakerGenerator::class, function ()
		{
			return FakerFactory::create();
		} );

		BindingBuilder::addServiceBindingResolver( EloquentFactory::class, function ()
		{
			$faker = BindingBuilder::resolveBinding( FakerGenerator::class );

			return EloquentFactory::construct( $faker, Framework::fw()->buildPath( '__database', 'factories' ) );
		} );
	}

	/**
	 * Register the queueable entity resolver implementation.
	 *
	 * @return void
	 */
	protected function registerQueueableEntityResolver()
	{
		BindingBuilder::addServiceBindingResolver( EntityResolver::class, function ()
		{
			return new QueueEntityResolver;
		} );
	}
}
