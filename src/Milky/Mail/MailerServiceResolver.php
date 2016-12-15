<?php namespace Milky\Mail;

use Milky\Binding\ServiceResolver;
use Milky\Binding\UniversalBuilder;
use Milky\Facades\Config;
use Milky\Http\View\ViewFactory;

/**
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class MailerServiceResolver extends ServiceResolver
{
	private $mailerInstance;

	public function __construct()
	{
		$this->setDefault( 'mailer' );

		$this->addClassAlias( Mailer::class, 'mailer' );
		$this->addClassAlias( \Swift_Mailer::class, 'swiftMailer' );
		$this->addClassAlias( TransportManager::class, 'swiftTransport' );
	}

	// mailer.mailer
	public function mailer()
	{
		if ( is_null( $this->mailerInstance ) )
		{
			// Once we have create the mailer instance, we will set a container instance
			// on the mailer. This allows us to resolve mailer classes via containers
			// for maximum testability on said classes instead of passing Closures.
			$mailer = new Mailer( ViewFactory::i(), $this->swiftMailer() );

			if ( $queue = UniversalBuilder::resolve( 'queue.connection' ) )
				$mailer->setQueue( $queue );

			// If a "from" address is set, we will set it on the mailer so that all mail
			// messages sent by the applications will utilize the same "from" address
			// on each one, which makes the developer's life a lot more convenient.
			$from = Config::get( 'mail.from' );

			if ( is_array( $from ) && isset( $from['address'] ) )
				$mailer->alwaysFrom( $from['address'], $from['name'] );

			$to = Config::get( 'mail.to' );

			if ( is_array( $to ) && isset( $to['address'] ) )
				$mailer->alwaysTo( $to['address'], $to['name'] );

			$this->mailerInstance = $mailer;
		}

		return $this->mailerInstance;
	}

	// mailer.swift.mailer
	public function swiftMailer()
	{
		return new \Swift_Mailer( $this->swiftTransport()->driver() );
	}

	// mailer.swift.transport
	public function swiftTransport()
	{
		return new TransportManager();
	}

	public function key()
	{
		return 'mailer';
	}
}
