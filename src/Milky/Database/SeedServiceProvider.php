<?php namespace Milky\Database;

use Milky\Binding\BindingBuilder;
use Milky\Database\Console\Seeds\SeedCommand;
use Milky\Framework;
use Milky\Providers\ServiceProvider;

class SeedServiceProvider extends ServiceProvider
{
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		Framework::set( 'seeder', BindingBuilder::resolveBinding( Seeder::class ) );

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
		BindingBuilder::addServiceBindingResolver( 'command.seed', function ( $app )
		{
			return new SeedCommand( $app['db'] );
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
