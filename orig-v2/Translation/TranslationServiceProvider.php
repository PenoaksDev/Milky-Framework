<?php
namespace Penoaks\Translation;

use Penoaks\Barebones\ServiceProvider;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class TranslationServiceProvider extends ServiceProvider
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
		$this->registerLoader();

		$this->bindings->singleton( 'translator', function ( $bindings )
		{
			$loader = $bindings['translation.loader'];

			// When registering the translator component, we'll need to set the default
			// locale as well as the fallback locale. So, we'll grab the application
			// configuration so we can easily get both of these values from there.
			$locale = $bindings['config']['app.locale'];

			$trans = new Translator( $loader, $locale );

			$trans->setFallback( $bindings['config']['app.fallback_locale'] );

			return $trans;
		} );
	}

	/**
	 * Register the translation line loader.
	 *
	 * @return void
	 */
	protected function registerLoader()
	{
		$this->bindings->singleton( 'translation.loader', function ( $bindings )
		{
			return new FileLoader( $bindings['files'], $bindings['path.lang'] );
		} );
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return ['translator', 'translation.loader'];
	}
}
