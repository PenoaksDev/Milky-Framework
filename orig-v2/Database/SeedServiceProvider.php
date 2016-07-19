<?php
namespace Penoaks\Database;

use Penoaks\Barebones\ServiceProvider;
use Penoaks\Database\Console\Seeds\SeedCommand;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class SeedServiceProvider extends ServiceProvider
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
		/*$this->bindings->singleton( 'seeder', function ()
		{
			return new Seeder;
		} );*/

		$this->registerSeedCommand();

		$this->commands( 'command.seed' );
	}

	/**
	 * Register the seed console command.
	 *
	 * @return void
	 */
	protected function registerSeedCommand()
	{
		$this->bindings->singleton( 'command.seed', function ( $fw )
		{
			return new SeedCommand( $fw->bindings['db'] );
		} );
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return ['seeder', 'command.seed'];
	}
}
