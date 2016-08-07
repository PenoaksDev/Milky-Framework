<?php namespace Milky\Translation;

use Milky\Binding\ServiceResolver;
use Milky\Facades\Config;
use Milky\Filesystem\Filesystem;
use Milky\Framework;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class TranslationServiceResolver extends ServiceResolver
{
	private $translatorInstance;

	public function __construct()
	{
		$this->setDefault( 'translator' );

		$this->addClassAlias( Translator::class, 'translator' );
		$this->addClassAlias( LoaderInterface::class, 'loader' );
	}

	/**
	 * @return Translator
	 */
	public function translator()
	{
		if ( is_null( $this->translatorInstance ) )
		{
			$loader = new FileLoader( Filesystem::i(), Framework::fw()->buildPath( '__lang' ) );

			// When registering the translator component, we'll need to set the default
			// locale as well as the fallback locale. So, we'll grab the application
			// configuration so we can easily get both of these values from there.
			$this->translatorInstance = new Translator( $loader, Config::get( 'app.locale' ) );
			$this->translatorInstance->setFallback( Config::get( 'app.fallback_locale' ) );
		}

		return $this->translatorInstance;
	}

	/**
	 * @return LoaderInterface
	 */
	public function loader()
	{
		return $this->translator()->getLoader();
	}

	public function key()
	{
		return ['translation', 'translator'];
	}
}
