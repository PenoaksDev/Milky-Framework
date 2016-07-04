<?php

namespace Foundation\Providers;

use Foundation\Support\Composer;
use Foundation\Support\ServiceProvider;

class ComposerServiceProvider extends ServiceProvider
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
		$this->fw->bindings->singleton('composer', function ($fw)
{
			return new Composer($fw->bindings['files'], $fw->basePath());
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return ['composer'];
	}
}
