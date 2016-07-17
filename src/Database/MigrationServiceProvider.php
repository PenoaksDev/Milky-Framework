<?php
namespace Penoaks\Database;

use Penoaks\Barebones\ServiceProvider;
use Penoaks\Database\Console\Migrations\InstallCommand;
use Penoaks\Database\Console\Migrations\MigrateCommand;
use Penoaks\Database\Console\Migrations\MigrateMakeCommand;
use Penoaks\Database\Console\Migrations\RefreshCommand;
use Penoaks\Database\Console\Migrations\ResetCommand;
use Penoaks\Database\Console\Migrations\RollbackCommand;
use Penoaks\Database\Console\Migrations\StatusCommand;
use Penoaks\Database\Migrations\DatabaseMigrationRepository;
use Penoaks\Database\Migrations\MigrationCreator;
use Penoaks\Database\Migrations\Migrator;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class MigrationServiceProvider extends ServiceProvider
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
		$this->registerRepository();

		// Once we have registered the migrator instance we will go ahead and register
		// all of the migration related commands that are used by the "Artisan" CLI
		// so that they may be easily accessed for registering with the consoles.
		$this->registerMigrator();

		$this->registerCreator();

		$this->registerCommands();
	}

	/**
	 * Register the migration repository service.
	 *
	 * @return void
	 */
	protected function registerRepository()
	{
		$this->bindings->singleton( 'migration.repository', function ( $bindings )
		{
			$table = $bindings['config']['database.migrations'];

			return new DatabaseMigrationRepository( $bindings['db'], $table );
		} );
	}

	/**
	 * Register the migrator service.
	 *
	 * @return void
	 */
	protected function registerMigrator()
	{
		// The migrator is responsible for actually running and rollback the migration
		// files in the application. We'll pass in our database connection resolver
		// so the migrator can resolve any of these connections when it needs to.
		$this->bindings->singleton( 'migrator', function ( $bindings )
		{
			$repository = $bindings['migration.repository'];

			return new Migrator( $repository, $bindings['db'], $bindings['files'] );
		} );
	}

	/**
	 * Register the migration creator.
	 *
	 * @return void
	 */
	protected function registerCreator()
	{
		$this->bindings->singleton( 'migration.creator', function ( $bindings )
		{
			return new MigrationCreator( $bindings['files'] );
		} );
	}

	/**
	 * Register all of the migration commands.
	 *
	 * @return void
	 */
	protected function registerCommands()
	{
		$commands = ['Migrate', 'Rollback', 'Reset', 'Refresh', 'Install', 'Make', 'Status'];

		// We'll simply spin through the list of commands that are migration related
		// and register each one of them with an application bindings. They will
		// be resolved in the Artisan start file and registered on the console.
		foreach ( $commands as $command )
		{
			$this->{'register' . $command . 'Command'}();
		}

		// Once the commands are registered in the application IoC bindings we will
		// register them with the Artisan start event so that these are available
		// when the Artisan application actually starts up and is getting used.
		$this->commands( 'command.migrate', 'command.migrate.make', 'command.migrate.install', 'command.migrate.rollback', 'command.migrate.reset', 'command.migrate.refresh', 'command.migrate.status' );
	}

	/**
	 * Register the "migrate" migration command.
	 *
	 * @return void
	 */
	protected function registerMigrateCommand()
	{
		$this->bindings->singleton( 'command.migrate', function ( $bindings )
		{
			return new MigrateCommand( $bindings['migrator'] );
		} );
	}

	/**
	 * Register the "rollback" migration command.
	 *
	 * @return void
	 */
	protected function registerRollbackCommand()
	{
		$this->bindings->singleton( 'command.migrate.rollback', function ( $bindings )
		{
			return new RollbackCommand( $bindings['migrator'] );
		} );
	}

	/**
	 * Register the "reset" migration command.
	 *
	 * @return void
	 */
	protected function registerResetCommand()
	{
		$this->bindings->singleton( 'command.migrate.reset', function ( $bindings )
		{
			return new ResetCommand( $bindings['migrator'] );
		} );
	}

	/**
	 * Register the "refresh" migration command.
	 *
	 * @return void
	 */
	protected function registerRefreshCommand()
	{
		$this->bindings->singleton( 'command.migrate.refresh', function ()
		{
			return new RefreshCommand;
		} );
	}

	/**
	 * Register the "make" migration command.
	 *
	 * @return void
	 */
	protected function registerMakeCommand()
	{
		$this->bindings->singleton( 'command.migrate.make', function ( $bindings )
		{
			// Once we have the migration creator registered, we will create the command
			// and inject the creator. The creator is responsible for the actual file
			// creation of the migrations, and may be extended by these developers.
			$creator = $bindings['migration.creator'];

			$composer = $bindings['composer'];

			return new MigrateMakeCommand( $creator, $composer );
		} );
	}

	/**
	 * Register the "status" migration command.
	 *
	 * @return void
	 */
	protected function registerStatusCommand()
	{
		$this->bindings->singleton( 'command.migrate.status', function ( $bindings )
		{
			return new StatusCommand( $bindings['migrator'] );
		} );
	}

	/**
	 * Register the "install" migration command.
	 *
	 * @return void
	 */
	protected function registerInstallCommand()
	{
		$this->bindings->singleton( 'command.migrate.install', function ( $bindings )
		{
			return new InstallCommand( $bindings['migration.repository'] );
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
			'migrator',
			'migration.repository',
			'command.migrate',
			'command.migrate.rollback',
			'command.migrate.reset',
			'command.migrate.refresh',
			'command.migrate.install',
			'command.migrate.status',
			'migration.creator',
			'command.migrate.make',
		];
	}
}
