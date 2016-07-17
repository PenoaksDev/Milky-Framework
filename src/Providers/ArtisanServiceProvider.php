<?php
namespace Penoaks\Providers;

use Penoaks\Auth\Console\ClearResetsCommand;
use Penoaks\Auth\Console\MakeAuthCommand;
use Penoaks\Barebones\ServiceProvider;
use Penoaks\Cache\Console\CacheTableCommand;
use Penoaks\Console\AppNameCommand;
use Penoaks\Console\ClearCompiledCommand;
use Penoaks\Console\ConfigCacheCommand;
use Penoaks\Console\ConfigClearCommand;
use Penoaks\Console\ConsoleMakeCommand;
use Penoaks\Console\DownCommand;
use Penoaks\Console\EnvironmentCommand;
use Penoaks\Console\EventGenerateCommand;
use Penoaks\Console\EventMakeCommand;
use Penoaks\Console\JobMakeCommand;
use Penoaks\Console\KeyGenerateCommand;
use Penoaks\Console\ListenerMakeCommand;
use Penoaks\Console\ModelMakeCommand;
use Penoaks\Console\OptimizeCommand;
use Penoaks\Console\PolicyMakeCommand;
use Penoaks\Console\ProviderMakeCommand;
use Penoaks\Console\RequestMakeCommand;
use Penoaks\Console\RouteCacheCommand;
use Penoaks\Console\RouteClearCommand;
use Penoaks\Console\RouteListCommand;
use Penoaks\Console\ServeCommand;
use Penoaks\Console\TestMakeCommand;
use Penoaks\Console\TinkerCommand;
use Penoaks\Console\UpCommand;
use Penoaks\Console\VendorPublishCommand;
use Penoaks\Console\ViewClearCommand;
use Penoaks\Database\Console\Seeds\SeederMakeCommand;
use Penoaks\Queue\Console\FailedTableCommand;
use Penoaks\Queue\Console\TableCommand;
use Penoaks\Routing\Console\ControllerMakeCommand;
use Penoaks\Routing\Console\MiddlewareMakeCommand;
use Penoaks\Session\Console\SessionTableCommand;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class ArtisanServiceProvider extends ServiceProvider
{
	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * The commands to be registered.
	 *
	 * @var array
	 */
	protected $commands = [
		'ClearCompiled' => 'command.clear-compiled',
		'ClearResets' => 'command.auth.resets.clear',
		'ConfigCache' => 'command.config.cache',
		'ConfigClear' => 'command.config.clear',
		'Down' => 'command.down',
		'Environment' => 'command.environment',
		'KeyGenerate' => 'command.key.generate',
		'Optimize' => 'command.optimize',
		'RouteCache' => 'command.route.cache',
		'RouteClear' => 'command.route.clear',
		'RouteList' => 'command.route.list',
		'Tinker' => 'command.tinker',
		'Up' => 'command.up',
		'ViewClear' => 'command.view.clear',
	];

	/**
	 * The commands to be registered.
	 *
	 * @var array
	 */
	protected $devCommands = [
		'AppName' => 'command.app.name',
		'AuthMake' => 'command.auth.make',
		'CacheTable' => 'command.cache.table',
		'ConsoleMake' => 'command.console.make',
		'ControllerMake' => 'command.controller.make',
		'EventGenerate' => 'command.event.generate',
		'EventMake' => 'command.event.make',
		'JobMake' => 'command.job.make',
		'ListenerMake' => 'command.listener.make',
		'MiddlewareMake' => 'command.middleware.make',
		'ModelMake' => 'command.model.make',
		'PolicyMake' => 'command.policy.make',
		'ProviderMake' => 'command.provider.make',
		'QueueFailedTable' => 'command.queue.failed-table',
		'QueueTable' => 'command.queue.table',
		'RequestMake' => 'command.request.make',
		'SeederMake' => 'command.seeder.make',
		'SessionTable' => 'command.session.table',
		'Serve' => 'command.serve',
		'TestMake' => 'command.test.make',
		'VendorPublish' => 'command.vendor.publish',
	];

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerCommands( $this->commands );

		$this->registerCommands( $this->devCommands );
	}

	/**
	 * Register the given commands.
	 *
	 * @param  array $commands
	 * @return void
	 */
	protected function registerCommands( array $commands )
	{
		foreach ( array_keys( $commands ) as $command )
		{
			$method = "register{$command}Command";

			call_user_func_array( [$this, $method], [] );
		}

		$this->commands( array_values( $commands ) );
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerAppNameCommand()
	{
		$this->bindings->singleton( 'command.app.name', function ( $bindings )
		{
			return new AppNameCommand( $bindings['composer'], $bindings['files'] );
		} );
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerAuthMakeCommand()
	{
		$this->bindings->singleton( 'command.auth.make', function ( $bindings )
		{
			return new MakeAuthCommand;
		} );
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerCacheTableCommand()
	{
		$this->bindings->singleton( 'command.cache.table', function ( $bindings )
		{
			return new CacheTableCommand( $bindings['files'], $bindings['composer'] );
		} );
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerClearCompiledCommand()
	{
		$this->bindings->singleton( 'command.clear-compiled', function ()
		{
			return new ClearCompiledCommand;
		} );
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerClearResetsCommand()
	{
		$this->bindings->singleton( 'command.auth.resets.clear', function ()
		{
			return new ClearResetsCommand;
		} );
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerConfigCacheCommand()
	{
		$this->bindings->singleton( 'command.config.cache', function ( $bindings )
		{
			return new ConfigCacheCommand( $bindings['files'] );
		} );
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerConfigClearCommand()
	{
		$this->bindings->singleton( 'command.config.clear', function ( $bindings )
		{
			return new ConfigClearCommand( $bindings['files'] );
		} );
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerConsoleMakeCommand()
	{
		$this->bindings->singleton( 'command.console.make', function ( $bindings )
		{
			return new ConsoleMakeCommand( $bindings['files'] );
		} );
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerControllerMakeCommand()
	{
		$this->bindings->singleton( 'command.controller.make', function ( $bindings )
		{
			return new ControllerMakeCommand( $bindings['files'] );
		} );
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerEventGenerateCommand()
	{
		$this->bindings->singleton( 'command.event.generate', function ()
		{
			return new EventGenerateCommand;
		} );
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerEventMakeCommand()
	{
		$this->bindings->singleton( 'command.event.make', function ( $bindings )
		{
			return new EventMakeCommand( $bindings['files'] );
		} );
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerDownCommand()
	{
		$this->bindings->singleton( 'command.down', function ()
		{
			return new DownCommand;
		} );
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerEnvironmentCommand()
	{
		$this->bindings->singleton( 'command.environment', function ()
		{
			return new EnvironmentCommand;
		} );
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerJobMakeCommand()
	{
		$this->bindings->singleton( 'command.job.make', function ( $bindings )
		{
			return new JobMakeCommand( $bindings['files'] );
		} );
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerKeyGenerateCommand()
	{
		$this->bindings->singleton( 'command.key.generate', function ()
		{
			return new KeyGenerateCommand;
		} );
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerListenerMakeCommand()
	{
		$this->bindings->singleton( 'command.listener.make', function ( $bindings )
		{
			return new ListenerMakeCommand( $bindings['files'] );
		} );
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerMiddlewareMakeCommand()
	{
		$this->bindings->singleton( 'command.middleware.make', function ( $bindings )
		{
			return new MiddlewareMakeCommand( $bindings['files'] );
		} );
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerModelMakeCommand()
	{
		$this->bindings->singleton( 'command.model.make', function ( $bindings )
		{
			return new ModelMakeCommand( $bindings['files'] );
		} );
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerOptimizeCommand()
	{
		$this->bindings->singleton( 'command.optimize', function ( $bindings )
		{
			return new OptimizeCommand( $bindings['composer'] );
		} );
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerProviderMakeCommand()
	{
		$this->bindings->singleton( 'command.provider.make', function ( $bindings )
		{
			return new ProviderMakeCommand( $bindings['files'] );
		} );
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerQueueFailedTableCommand()
	{
		$this->bindings->singleton( 'command.queue.failed-table', function ( $bindings )
		{
			return new FailedTableCommand( $bindings['files'], $bindings['composer'] );
		} );
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerQueueTableCommand()
	{
		$this->bindings->singleton( 'command.queue.table', function ( $bindings )
		{
			return new TableCommand( $bindings['files'], $bindings['composer'] );
		} );
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerRequestMakeCommand()
	{
		$this->bindings->singleton( 'command.request.make', function ( $bindings )
		{
			return new RequestMakeCommand( $bindings['files'] );
		} );
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerSeederMakeCommand()
	{
		$this->bindings->singleton( 'command.seeder.make', function ( $bindings )
		{
			return new SeederMakeCommand( $bindings['files'], $bindings['composer'] );
		} );
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerSessionTableCommand()
	{
		$this->bindings->singleton( 'command.session.table', function ( $bindings )
		{
			return new SessionTableCommand( $bindings['files'], $bindings['composer'] );
		} );
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerRouteCacheCommand()
	{
		$this->bindings->singleton( 'command.route.cache', function ( $bindings )
		{
			return new RouteCacheCommand( $bindings['files'] );
		} );
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerRouteClearCommand()
	{
		$this->bindings->singleton( 'command.route.clear', function ( $bindings )
		{
			return new RouteClearCommand( $bindings['files'] );
		} );
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerRouteListCommand()
	{
		$this->bindings->singleton( 'command.route.list', function ( $bindings )
		{
			return new RouteListCommand( $bindings['router'] );
		} );
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerServeCommand()
	{
		$this->bindings->singleton( 'command.serve', function ()
		{
			return new ServeCommand;
		} );
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerTestMakeCommand()
	{
		$this->bindings->singleton( 'command.test.make', function ( $bindings )
		{
			return new TestMakeCommand( $bindings['files'] );
		} );
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerTinkerCommand()
	{
		$this->bindings->singleton( 'command.tinker', function ()
		{
			return new TinkerCommand;
		} );
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerUpCommand()
	{
		$this->bindings->singleton( 'command.up', function ()
		{
			return new UpCommand;
		} );
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerVendorPublishCommand()
	{
		$this->bindings->singleton( 'command.vendor.publish', function ( $bindings )
		{
			return new VendorPublishCommand( $bindings['files'] );
		} );
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerViewClearCommand()
	{
		$this->bindings->singleton( 'command.view.clear', function ( $bindings )
		{
			return new ViewClearCommand( $bindings['files'] );
		} );
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerPolicyMakeCommand()
	{
		$this->bindings->singleton( 'command.policy.make', function ( $bindings )
		{
			return new PolicyMakeCommand( $bindings['files'] );
		} );
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		if ( $this->fw->environment( 'production' ) )
		{
			return array_values( $this->commands );
		}
		else
		{
			return array_merge( array_values( $this->commands ), array_values( $this->devCommands ) );
		}
	}
}
