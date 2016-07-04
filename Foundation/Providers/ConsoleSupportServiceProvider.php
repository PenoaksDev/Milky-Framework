<?php

namespace Foundation\Providers;

use Foundation\Support\AggregateServiceProvider;

class ConsoleSupportServiceProvider extends AggregateServiceProvider
{
	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * The provider class names.
	 *
	 * @var array
	 */
	protected $providers = [
		'Foundation\Providers\ArtisanServiceProvider',
		'Foundation\Console\ScheduleServiceProvider',
		'Foundation\Database\MigrationServiceProvider',
		'Foundation\Database\SeedServiceProvider',
		'Foundation\Providers\ComposerServiceProvider',
		'Foundation\Queue\ConsoleServiceProvider',
	];
}
