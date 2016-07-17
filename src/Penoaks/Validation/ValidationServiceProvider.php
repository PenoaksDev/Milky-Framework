<?php
namespace Penoaks\Validation;

use Penoaks\Barebones\ServiceProvider;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class ValidationServiceProvider extends ServiceProvider
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
		$this->registerPresenceVerifier();

		$this->registerValidationFactory();
	}

	/**
	 * Register the validation factory.
	 *
	 * @return void
	 */
	protected function registerValidationFactory()
	{
		$this->bindings->singleton( 'validator', function ( $bindings )
		{
			$validator = new Factory( $bindings['translator'], $bindings );

			// The validation presence verifier is responsible for determining the existence
			// of values in a given data collection, typically a relational database or
			// other persistent data stores. And it is used to check for uniqueness.
			if ( isset( $bindings['validation.presence'] ) )
			{
				$validator->setPresenceVerifier( $bindings['validation.presence'] );
			}

			return $validator;
		} );
	}

	/**
	 * Register the database presence verifier.
	 *
	 * @return void
	 */
	protected function registerPresenceVerifier()
	{
		$this->bindings->singleton( 'validation.presence', function ( $bindings )
		{
			return new DatabasePresenceVerifier( $bindings['db'] );
		} );
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [
			'validator',
			'validation.presence',
		];
	}
}
