<?php

namesapce Penoaks\Database;

use Foundation\Support\ServiceProvider;
use Foundation\Database\Console\Seeds\SeedCommand;

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
		$this->fw->bindings->singleton('seeder', function ()
{
			return new Seeder;
		});

		$this->registerSeedCommand();

		$this->commands('command.seed');
	}

	/**
	 * Register the seed console command.
	 *
	 * @return void
	 */
	protected function registerSeedCommand()
	{
		$this->fw->bindings->singleton('command.seed', function ($fw)
{
			return new SeedCommand($fw->bindings['db']);
		});
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
