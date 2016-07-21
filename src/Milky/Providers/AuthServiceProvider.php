<?php namespace Milky\Providers;

use Milky\Auth\Access\Gate;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
abstract class AuthServiceProvider extends ServiceProvider
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
	 * @param  Gate $gate
	 */
	public function registerPolicies( Gate $gate )
	{
		foreach ( $this->policies as $key => $value )
		{
			$gate->policy( $key, $value );
		}
	}

	/**
	 * {@inheritdoc
	 */
	public function register()
	{

	}
}
