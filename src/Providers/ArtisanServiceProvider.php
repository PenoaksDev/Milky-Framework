<?php
namesapce Penoaks\Providers;

use Foundation\Barebones\ServiceProvider;
use Foundation\Queue\Console\TableCommand;
use Foundation\Auth\Console\MakeAuthCommand;
use Foundation\Console\UpCommand;
use Foundation\Console\DownCommand;
use Foundation\Auth\Console\ClearResetsCommand;
use Foundation\Console\ServeCommand;
use Foundation\Cache\Console\CacheTableCommand;
use Foundation\Queue\Console\FailedTableCommand;
use Foundation\Console\TinkerCommand;
use Foundation\Console\JobMakeCommand;
use Foundation\Console\AppNameCommand;
use Foundation\Console\OptimizeCommand;
use Foundation\Console\TestMakeCommand;
use Foundation\Console\RouteListCommand;
use Foundation\Console\EventMakeCommand;
use Foundation\Console\ModelMakeCommand;
use Foundation\Console\ViewClearCommand;
use Foundation\Session\Console\SessionTableCommand;
use Foundation\Console\PolicyMakeCommand;
use Foundation\Console\RouteCacheCommand;
use Foundation\Console\RouteClearCommand;
use Foundation\Routing\Console\ControllerMakeCommand;
use Foundation\Routing\Console\MiddlewareMakeCommand;
use Foundation\Console\ConfigCacheCommand;
use Foundation\Console\ConfigClearCommand;
use Foundation\Console\ConsoleMakeCommand;
use Foundation\Console\EnvironmentCommand;
use Foundation\Console\KeyGenerateCommand;
use Foundation\Console\RequestMakeCommand;
use Foundation\Console\ListenerMakeCommand;
use Foundation\Console\ProviderMakeCommand;
use Foundation\Console\ClearCompiledCommand;
use Foundation\Console\EventGenerateCommand;
use Foundation\Console\VendorPublishCommand;
use Foundation\Database\Console\Seeds\SeederMakeCommand;

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
		$this->registerCommands($this->commands);

		$this->registerCommands($this->devCommands);
	}

	/**
	 * Register the given commands.
	 *
	 * @param  array  $commands
	 * @return void
	 */
	protected function registerCommands(array $commands)
	{
		foreach (array_keys($commands) as $command)
{
			$method = "register{$command}Command";

			call_user_func_array([$this, $method], []);
		}

		$this->commands(array_values($commands));
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerAppNameCommand()
	{
		$this->fw->bindings->singleton('command.app.name', function ($fw)
{
			return new AppNameCommand($fw->bindings['composer'], $fw->bindings['files']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerAuthMakeCommand()
	{
		$this->fw->bindings->singleton('command.auth.make', function ($fw)
{
			return new MakeAuthCommand;
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerCacheTableCommand()
	{
		$this->fw->bindings->singleton('command.cache.table', function ($fw)
{
			return new CacheTableCommand($fw->bindings['files'], $fw->bindings['composer']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerClearCompiledCommand()
	{
		$this->fw->bindings->singleton('command.clear-compiled', function ()
{
			return new ClearCompiledCommand;
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerClearResetsCommand()
	{
		$this->fw->bindings->singleton('command.auth.resets.clear', function ()
{
			return new ClearResetsCommand;
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerConfigCacheCommand()
	{
		$this->fw->bindings->singleton('command.config.cache', function ($fw)
{
			return new ConfigCacheCommand($fw->bindings['files']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerConfigClearCommand()
	{
		$this->fw->bindings->singleton('command.config.clear', function ($fw)
{
			return new ConfigClearCommand($fw->bindings['files']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerConsoleMakeCommand()
	{
		$this->fw->bindings->singleton('command.console.make', function ($fw)
{
			return new ConsoleMakeCommand($fw->bindings['files']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerControllerMakeCommand()
	{
		$this->fw->bindings->singleton('command.controller.make', function ($fw)
{
			return new ControllerMakeCommand($fw->bindings['files']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerEventGenerateCommand()
	{
		$this->fw->bindings->singleton('command.event.generate', function ()
{
			return new EventGenerateCommand;
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerEventMakeCommand()
	{
		$this->fw->bindings->singleton('command.event.make', function ($fw)
{
			return new EventMakeCommand($fw->bindings['files']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerDownCommand()
	{
		$this->fw->bindings->singleton('command.down', function ()
{
			return new DownCommand;
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerEnvironmentCommand()
	{
		$this->fw->bindings->singleton('command.environment', function ()
{
			return new EnvironmentCommand;
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerJobMakeCommand()
	{
		$this->fw->bindings->singleton('command.job.make', function ($fw)
{
			return new JobMakeCommand($fw->bindings['files']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerKeyGenerateCommand()
	{
		$this->fw->bindings->singleton('command.key.generate', function ()
{
			return new KeyGenerateCommand;
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerListenerMakeCommand()
	{
		$this->fw->bindings->singleton('command.listener.make', function ($fw)
{
			return new ListenerMakeCommand($fw->bindings['files']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerMiddlewareMakeCommand()
	{
		$this->fw->bindings->singleton('command.middleware.make', function ($fw)
{
			return new MiddlewareMakeCommand($fw->bindings['files']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerModelMakeCommand()
	{
		$this->fw->bindings->singleton('command.model.make', function ($fw)
{
			return new ModelMakeCommand($fw->bindings['files']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerOptimizeCommand()
	{
		$this->fw->bindings->singleton('command.optimize', function ($fw)
{
			return new OptimizeCommand($fw->bindings['composer']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerProviderMakeCommand()
	{
		$this->fw->bindings->singleton('command.provider.make', function ($fw)
{
			return new ProviderMakeCommand($fw->bindings['files']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerQueueFailedTableCommand()
	{
		$this->fw->bindings->singleton('command.queue.failed-table', function ($fw)
{
			return new FailedTableCommand($fw->bindings['files'], $fw->bindings['composer']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerQueueTableCommand()
	{
		$this->fw->bindings->singleton('command.queue.table', function ($fw)
{
			return new TableCommand($fw->bindings['files'], $fw->bindings['composer']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerRequestMakeCommand()
	{
		$this->fw->bindings->singleton('command.request.make', function ($fw)
{
			return new RequestMakeCommand($fw->bindings['files']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerSeederMakeCommand()
	{
		$this->fw->bindings->singleton('command.seeder.make', function ($fw)
{
			return new SeederMakeCommand($fw->bindings['files'], $fw->bindings['composer']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerSessionTableCommand()
	{
		$this->fw->bindings->singleton('command.session.table', function ($fw)
{
			return new SessionTableCommand($fw->bindings['files'], $fw->bindings['composer']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerRouteCacheCommand()
	{
		$this->fw->bindings->singleton('command.route.cache', function ($fw)
{
			return new RouteCacheCommand($fw->bindings['files']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerRouteClearCommand()
	{
		$this->fw->bindings->singleton('command.route.clear', function ($fw)
{
			return new RouteClearCommand($fw->bindings['files']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerRouteListCommand()
	{
		$this->fw->bindings->singleton('command.route.list', function ($fw)
{
			return new RouteListCommand($fw->bindings['router']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerServeCommand()
	{
		$this->fw->bindings->singleton('command.serve', function ()
{
			return new ServeCommand;
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerTestMakeCommand()
	{
		$this->fw->bindings->singleton('command.test.make', function ($fw)
{
			return new TestMakeCommand($fw->bindings['files']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerTinkerCommand()
	{
		$this->fw->bindings->singleton('command.tinker', function ()
{
			return new TinkerCommand;
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerUpCommand()
	{
		$this->fw->bindings->singleton('command.up', function ()
{
			return new UpCommand;
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerVendorPublishCommand()
	{
		$this->fw->bindings->singleton('command.vendor.publish', function ($fw)
{
			return new VendorPublishCommand($fw->bindings['files']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerViewClearCommand()
	{
		$this->fw->bindings->singleton('command.view.clear', function ($fw)
{
			return new ViewClearCommand($fw->bindings['files']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerPolicyMakeCommand()
	{
		$this->fw->bindings->singleton('command.policy.make', function ($fw)
{
			return new PolicyMakeCommand($fw->bindings['files']);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		if ($this->fw->environment('production'))
{
			return array_values($this->commands);
		}
else
{
			return array_merge(array_values($this->commands), array_values($this->devCommands));
		}
	}
}
