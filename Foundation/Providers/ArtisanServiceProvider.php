<?php

namespace Foundation\Providers;

use Foundation\Support\ServiceProvider;
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
		foreach (array_keys($commands) as $command) {
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
		$this->app->singleton('command.app.name', function ($app) {
			return new AppNameCommand($app['composer'], $app['files']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerAuthMakeCommand()
	{
		$this->app->singleton('command.auth.make', function ($app) {
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
		$this->app->singleton('command.cache.table', function ($app) {
			return new CacheTableCommand($app['files'], $app['composer']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerClearCompiledCommand()
	{
		$this->app->singleton('command.clear-compiled', function () {
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
		$this->app->singleton('command.auth.resets.clear', function () {
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
		$this->app->singleton('command.config.cache', function ($app) {
			return new ConfigCacheCommand($app['files']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerConfigClearCommand()
	{
		$this->app->singleton('command.config.clear', function ($app) {
			return new ConfigClearCommand($app['files']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerConsoleMakeCommand()
	{
		$this->app->singleton('command.console.make', function ($app) {
			return new ConsoleMakeCommand($app['files']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerControllerMakeCommand()
	{
		$this->app->singleton('command.controller.make', function ($app) {
			return new ControllerMakeCommand($app['files']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerEventGenerateCommand()
	{
		$this->app->singleton('command.event.generate', function () {
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
		$this->app->singleton('command.event.make', function ($app) {
			return new EventMakeCommand($app['files']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerDownCommand()
	{
		$this->app->singleton('command.down', function () {
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
		$this->app->singleton('command.environment', function () {
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
		$this->app->singleton('command.job.make', function ($app) {
			return new JobMakeCommand($app['files']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerKeyGenerateCommand()
	{
		$this->app->singleton('command.key.generate', function () {
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
		$this->app->singleton('command.listener.make', function ($app) {
			return new ListenerMakeCommand($app['files']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerMiddlewareMakeCommand()
	{
		$this->app->singleton('command.middleware.make', function ($app) {
			return new MiddlewareMakeCommand($app['files']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerModelMakeCommand()
	{
		$this->app->singleton('command.model.make', function ($app) {
			return new ModelMakeCommand($app['files']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerOptimizeCommand()
	{
		$this->app->singleton('command.optimize', function ($app) {
			return new OptimizeCommand($app['composer']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerProviderMakeCommand()
	{
		$this->app->singleton('command.provider.make', function ($app) {
			return new ProviderMakeCommand($app['files']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerQueueFailedTableCommand()
	{
		$this->app->singleton('command.queue.failed-table', function ($app) {
			return new FailedTableCommand($app['files'], $app['composer']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerQueueTableCommand()
	{
		$this->app->singleton('command.queue.table', function ($app) {
			return new TableCommand($app['files'], $app['composer']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerRequestMakeCommand()
	{
		$this->app->singleton('command.request.make', function ($app) {
			return new RequestMakeCommand($app['files']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerSeederMakeCommand()
	{
		$this->app->singleton('command.seeder.make', function ($app) {
			return new SeederMakeCommand($app['files'], $app['composer']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerSessionTableCommand()
	{
		$this->app->singleton('command.session.table', function ($app) {
			return new SessionTableCommand($app['files'], $app['composer']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerRouteCacheCommand()
	{
		$this->app->singleton('command.route.cache', function ($app) {
			return new RouteCacheCommand($app['files']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerRouteClearCommand()
	{
		$this->app->singleton('command.route.clear', function ($app) {
			return new RouteClearCommand($app['files']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerRouteListCommand()
	{
		$this->app->singleton('command.route.list', function ($app) {
			return new RouteListCommand($app['router']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerServeCommand()
	{
		$this->app->singleton('command.serve', function () {
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
		$this->app->singleton('command.test.make', function ($app) {
			return new TestMakeCommand($app['files']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerTinkerCommand()
	{
		$this->app->singleton('command.tinker', function () {
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
		$this->app->singleton('command.up', function () {
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
		$this->app->singleton('command.vendor.publish', function ($app) {
			return new VendorPublishCommand($app['files']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerViewClearCommand()
	{
		$this->app->singleton('command.view.clear', function ($app) {
			return new ViewClearCommand($app['files']);
		});
	}

	/**
	 * Register the command.
	 *
	 * @return void
	 */
	protected function registerPolicyMakeCommand()
	{
		$this->app->singleton('command.policy.make', function ($app) {
			return new PolicyMakeCommand($app['files']);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		if ($this->app->environment('production')) {
			return array_values($this->commands);
		} else {
			return array_merge(array_values($this->commands), array_values($this->devCommands));
		}
	}
}
