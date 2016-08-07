<?php namespace Milky\Validation;

use Milky\Binding\ServiceResolver;
use Milky\Database\DatabaseManager;
use Milky\Translation\Translator;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class ValidationServiceResolver extends ServiceResolver
{
	protected $factoryInstance;

	public function __construct()
	{
		$this->setDefault( 'factory' );

		$this->addClassAlias( ValidationFactory::class, 'factory' );
		$this->addClassAlias( PresenceVerifierInterface::class, 'presence' );
	}

	/**
	 * @return ValidationFactory
	 */
	public function factory()
	{
		if ( is_null( $this->factoryInstance ) )
		{
			$this->factoryInstance = new ValidationFactory( Translator::i() );

			// The validation presence verifier is responsible for determining the existence
			// of values in a given data collection, typically a relational database or
			// other persistent data stores. And it is used to check for uniqueness.
			$this->factoryInstance->setPresenceVerifier( new DatabasePresenceVerifier( DatabaseManager::i() ) );
		}

		return $this->factoryInstance;
	}

	/**
	 * @return PresenceVerifierInterface
	 */
	public function presence()
	{
		return $this->factory()->getPresenceVerifier();
	}

	public function key()
	{
		return ['validation', 'validator'];
	}
}
