<?php
namespace Penoaks\Database;

use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;
use Penoaks\Barebones\ServiceProvider;
use Penoaks\Database\Connectors\ConnectionFactory;
use Penoaks\Database\Eloquent\Factory as EloquentFactory;
use Penoaks\Database\Eloquent\Model;
use Penoaks\Database\Eloquent\QueueEntityResolver;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class DatabaseServiceProvider extends ServiceProvider
{
	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		Model::setConnectionResolver( $this->bindings['db'] );

		Model::setEventDispatcher( $this->bindings['events'] );
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
		$this->bindings->singleton( 'db.factory', function ( $bindings )
		{
			return new ConnectionFactory( $bindings );
		} );

		// The database manager is used to resolve various connections, since multiple
		// connections might be managed. It also implements the connection resolver
		// interface which may be used by other components requiring connections.
		$this->bindings->singleton( 'db', function ( $bindings )
		{
			return new DatabaseManager( $bindings, $bindings['db.factory'] );
		} );

		$this->bindings->bind( 'db.connection', function ( $bindings )
		{
			return $bindings['db']->connection();
		} );
	}

	/**
	 * Register the Eloquent factory instance in the bindings.
	 *
	 * @return void
	 */
	protected function registerEloquentFactory()
	{
		$this->bindings->singleton( FakerGenerator::class, function ()
		{
			return FakerFactory::create();
		} );

		$this->bindings->singleton( EloquentFactory::class, function ( $bindings )
		{
			$faker = $bindings->make( FakerGenerator::class );

			return EloquentFactory::construct( $faker, database_path( 'factories' ) );
		} );
	}

	/**
	 * Register the queueable entity resolver implementation.
	 *
	 * @return void
	 */
	protected function registerQueueableEntityResolver()
	{
		$this->bindings->singleton( 'Penoaks\Contracts\Queue\EntityResolver', function ()
		{
			return new QueueEntityResolver;
		} );
	}
}
