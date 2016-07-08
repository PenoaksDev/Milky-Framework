<?php

namesapce Penoaks\Pipeline;

use Foundation\Support\ServiceProvider;

class PipelineServiceProvider extends ServiceProvider
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
		$this->fw->bindings->singleton(
			'Penoaks\Contracts\Pipeline\Hub', 'Penoaks\Pipeline\Hub'
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
			'Penoaks\Contracts\Pipeline\Hub',
		];
	}
}
