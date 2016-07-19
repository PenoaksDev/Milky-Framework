<?php
namespace Penoaks\Queue;

use Penoaks\Barebones\ServiceProvider;
use Penoaks\Queue\Console\FlushFailedCommand;
use Penoaks\Queue\Console\ForgetFailedCommand;
use Penoaks\Queue\Console\ListFailedCommand;
use Penoaks\Queue\Console\RetryCommand;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
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
		$this->bindings->singleton( 'command.queue.failed', function ()
		{
			return new ListFailedCommand;
		} );

		$this->bindings->singleton( 'command.queue.retry', function ()
		{
			return new RetryCommand;
		} );

		$this->bindings->singleton( 'command.queue.forget', function ()
		{
			return new ForgetFailedCommand;
		} );

		$this->bindings->singleton( 'command.queue.flush', function ()
		{
			return new FlushFailedCommand;
		} );

		$this->commands( 'command.queue.failed', 'command.queue.retry', 'command.queue.forget', 'command.queue.flush' );
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [
			'command.queue.failed',
			'command.queue.retry',
			'command.queue.forget',
			'command.queue.flush',
		];
	}
}
