<?php

namespace Penoaks\Queue;

use Penoaks\Support\ServiceProvider;
use Penoaks\Queue\Console\RetryCommand;
use Penoaks\Queue\Console\ListFailedCommand;
use Penoaks\Queue\Console\FlushFailedCommand;
use Penoaks\Queue\Console\ForgetFailedCommand;

class ConsoleServiceProvider extends ServiceProvider
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
		$this->fw->bindings->singleton('command.queue.failed', function ()
{
			return new ListFailedCommand;
		});

		$this->fw->bindings->singleton('command.queue.retry', function ()
{
			return new RetryCommand;
		});

		$this->fw->bindings->singleton('command.queue.forget', function ()
{
			return new ForgetFailedCommand;
		});

		$this->fw->bindings->singleton('command.queue.flush', function ()
{
			return new FlushFailedCommand;
		});

		$this->commands(
			'command.queue.failed', 'command.queue.retry',
			'command.queue.forget', 'command.queue.flush'
		);
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [
			'command.queue.failed', 'command.queue.retry',
			'command.queue.forget', 'command.queue.flush',
		];
	}
}
