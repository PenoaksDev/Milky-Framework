<?php

namespace Penoaks\Console;

use Penoaks\Support\ServiceProvider;

class ScheduleServiceProvider extends ServiceProvider
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
		$this->commands('Penoaks\Console\Scheduling\ScheduleRunCommand');
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [
			'Penoaks\Console\Scheduling\ScheduleRunCommand',
		];
	}
}
