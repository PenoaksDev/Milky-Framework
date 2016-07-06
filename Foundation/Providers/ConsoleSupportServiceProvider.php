<?php
namespace Foundation\Providers;

use Foundation\Support\AggregateServiceProvider;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
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
