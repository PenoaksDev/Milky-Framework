<?php

namespace Foundation\Support\Providers;

use Foundation\Support\ServiceProvider;
use Foundation\Contracts\Auth\Access\Gate as GateContract;

class AuthServiceProvider extends ServiceProvider
{
	/**
	 * The policy mappings for the application.
	 *
	 * @var array
	 */
	protected $policies = [];

	/**
	 * Register the application's policies.
	 *
	 * @param  \Foundation\Contracts\Auth\Access\Gate  $gate
	 * @return void
	 */
	public function registerPolicies(GateContract $gate)
	{
		foreach ($this->policies as $key => $value)
{
			$gate->policy($key, $value);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		//
	}
}
