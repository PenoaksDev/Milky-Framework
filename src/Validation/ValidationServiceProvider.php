<?php

namespace Penoaks\Validation;

use Penoaks\Support\ServiceProvider;

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
		$this->fw->bindings->singleton('validator', function ($fw)
{
			$validator = new Factory($fw->bindings['translator'], $fw);

			// The validation presence verifier is responsible for determining the existence
			// of values in a given data collection, typically a relational database or
			// other persistent data stores. And it is used to check for uniqueness.
			if (isset($fw->bindings['validation.presence']))
{
				$validator->setPresenceVerifier($fw->bindings['validation.presence']);
			}

			return $validator;
		});
	}

	/**
	 * Register the database presence verifier.
	 *
	 * @return void
	 */
	protected function registerPresenceVerifier()
	{
		$this->fw->bindings->singleton('validation.presence', function ($fw)
{
			return new DatabasePresenceVerifier($fw->bindings['db']);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [
			'validator', 'validation.presence',
		];
	}
}
