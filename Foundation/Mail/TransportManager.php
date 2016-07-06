<?php
namespace Foundation\Mail;

use Aws\Ses\SesClient;
use Foundation\Support\Arr;
use Foundation\Support\Manager;
use GuzzleHttp\Client as HttpClient;
use Swift_SmtpTransport as SmtpTransport;
use Swift_MailTransport as MailTransport;
use Foundation\Mail\Transport\LogTransport;
use Foundation\Mail\Transport\SesTransport;
use Foundation\Mail\Transport\MailgunTransport;
use Foundation\Mail\Transport\MandrillTransport;
use Foundation\Mail\Transport\SparkPostTransport;
use Swift_SendmailTransport as SendmailTransport;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class TransportManager extends Manager
{
	/**
	 * Create an instance of the SMTP Swift Transport driver.
	 *
	 * @return \Swift_SmtpTransport
	 */
	protected function createSmtpDriver()
	{
		$config = $this->fw->bindings['config']['mail'];

		// The Swift SMTP transport instance will allow us to use any SMTP backend
		// for delivering mail such as Sendgrid, Amazon SES, or a custom server
		// a developer has available. We will just pass this configured host.
		$transport = SmtpTransport::newInstance(
			$config['host'], $config['port']
		);

		if (isset($config['encryption']))
{
			$transport->setEncryption($config['encryption']);
		}

		// Once we have the transport we will check for the presence of a username
		// and password. If we have it we will set the credentials on the Swift
		// transporter instance so that we'll properly authenticate delivery.
		if (isset($config['username']))
{
			$transport->setUsername($config['username']);

			$transport->setPassword($config['password']);
		}

		if (isset($config['stream']))
{
			$transport->setStreamOptions($config['stream']);
		}

		return $transport;
	}

	/**
	 * Create an instance of the Sendmail Swift Transport driver.
	 *
	 * @return \Swift_SendmailTransport
	 */
	protected function createSendmailDriver()
	{
		$command = $this->fw->bindings['config']['mail']['sendmail'];

		return SendmailTransport::newInstance($command);
	}

	/**
	 * Create an instance of the Amazon SES Swift Transport driver.
	 *
	 * @return \Swift_SendmailTransport
	 */
	protected function createSesDriver()
	{
		$config = $this->fw->bindings['config']->get('services.ses', []);

		$config += [
			'version' => 'latest', 'service' => 'email',
		];

		if ($config['key'] && $config['secret'])
{
			$config['credentials'] = Arr::only($config, ['key', 'secret']);
		}

		return new SesTransport(new SesClient($config));
	}

	/**
	 * Create an instance of the Mail Swift Transport driver.
	 *
	 * @return \Swift_MailTransport
	 */
	protected function createMailDriver()
	{
		return MailTransport::newInstance();
	}

	/**
	 * Create an instance of the Mailgun Swift Transport driver.
	 *
	 * @return \Foundation\Mail\Transport\MailgunTransport
	 */
	protected function createMailgunDriver()
	{
		$config = $this->fw->bindings['config']->get('services.mailgun', []);

		return new MailgunTransport(
			$this->getHttpClient($config),
			$config['secret'], $config['domain']
		);
	}

	/**
	 * Create an instance of the Mandrill Swift Transport driver.
	 *
	 * @return \Foundation\Mail\Transport\MandrillTransport
	 */
	protected function createMandrillDriver()
	{
		$config = $this->fw->bindings['config']->get('services.mandrill', []);

		return new MandrillTransport(
			$this->getHttpClient($config), $config['secret']
		);
	}

	/**
	 * Create an instance of the SparkPost Swift Transport driver.
	 *
	 * @return \Foundation\Mail\Transport\SparkPostTransport
	 */
	protected function createSparkPostDriver()
	{
		$config = $this->fw->bindings['config']->get('services.sparkpost', []);

		return new SparkPostTransport(
			$this->getHttpClient($config), $config['secret']
		);
	}

	/**
	 * Create an instance of the Log Swift Transport driver.
	 *
	 * @return \Foundation\Mail\Transport\LogTransport
	 */
	protected function createLogDriver()
	{
		return new LogTransport($this->fw->make('Psr\Log\LoggerInterface'));
	}

	/**
	 * Get a fresh Guzzle HTTP client instance.
	 *
	 * @param  array  $config
	 * @return HttpClient
	 */
	protected function getHttpClient($config)
	{
		$guzzleConfig = Arr::get($config, 'guzzle', []);

		return new HttpClient(Arr::add($guzzleConfig, 'connect_timeout', 60));
	}

	/**
	 * Get the default mail driver name.
	 *
	 * @return string
	 */
	public function getDefaultDriver()
	{
		return $this->fw->bindings['config']['mail.driver'];
	}

	/**
	 * Set the default mail driver name.
	 *
	 * @param  string  $name
	 * @return void
	 */
	public function setDefaultDriver($name)
	{
		$this->fw->bindings['config']['mail.driver'] = $name;
	}
}
