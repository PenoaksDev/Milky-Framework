<?php
namespace Penoaks\Mail\Transport;

use Aws\Ses\SesClient;
use Swift_Mime_Message;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class SesTransport extends Transport
{
	/**
	 * The Amazon SES instance.
	 *
	 * @var \Aws\Ses\SesClient
	 */
	protected $ses;

	/**
	 * Create a new SES transport instance.
	 *
	 * @param  \Aws\Ses\SesClient $ses
	 * @return void
	 */
	public function __construct( SesClient $ses )
	{
		$this->ses = $ses;
	}

	/**
	 * {@inheritdoc}
	 */
	public function send( Swift_Mime_Message $message, &$failedRecipients = null )
	{
		$this->beforeSendPerformed( $message );

		return $this->ses->sendRawEmail( [
			'Source' => key( $message->getSender() ?: $message->getFrom() ),
			'RawMessage' => [
				'Data' => $message->toString(),
			],
		] );
	}
}
