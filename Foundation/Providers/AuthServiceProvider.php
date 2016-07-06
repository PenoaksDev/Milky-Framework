<?php
namespace Foundation\Providers;

use Foundation\Barebones\ServiceProvider;
use Foundation\Contracts\Auth\Access\Gate as GateContract;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
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
	 * @param  \Foundation\Contracts\Auth\Access\Gate $gate
	 * @return void
	 */
	public function registerPolicies( GateContract $gate )
	{
		foreach ( $this->policies as $key => $value )
		{
			$gate->policy( $key, $value );
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
