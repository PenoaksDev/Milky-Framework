<?php

namespace Foundation\Mail;

use Swift_Mailer;
use Foundation\Support\ServiceProvider;

class MailServiceProvider extends ServiceProvider
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
		$this->registerSwiftMailer();

		$this->fw->bindings->singleton('mailer', function ($fw)
{
			// Once we have create the mailer instance, we will set a bindings instance
			// on the mailer. This allows us to resolve mailer classes via bindingss
			// for maximum testability on said classes instead of passing Closures.
			$mailer = new Mailer(
				$fw->bindings['view'], $fw->bindings['swift.mailer'], $fw->bindings['events']
			);

			$this->setMailerDependencies($mailer, $fw);

			// If a "from" address is set, we will set it on the mailer so that all mail
			// messages sent by the applications will utilize the same "from" address
			// on each one, which makes the developer's life a lot more convenient.
			$from = $fw->bindings['config']['mail.from'];

			if (is_array($from) && isset($from['address']))
{
				$mailer->alwaysFrom($from['address'], $from['name']);
			}

			$to = $fw->bindings['config']['mail.to'];

			if (is_array($to) && isset($to['address']))
{
				$mailer->alwaysTo($to['address'], $to['name']);
			}

			return $mailer;
		});
	}

	/**
	 * Set a few dependencies on the mailer instance.
	 *
	 * @param  \Foundation\Mail\Mailer  $mailer
	 * @param  \Foundation\Framework  $fw
	 * @return void
	 */
	protected function setMailerDependencies($mailer, $fw)
	{
		$mailer->setBindings($fw);

		if ($fw->bound('queue'))
{
			$mailer->setQueue($fw->bindings['queue.connection']);
		}
	}

	/**
	 * Register the Swift Mailer instance.
	 *
	 * @return void
	 */
	public function registerSwiftMailer()
	{
		$this->registerSwiftTransport();

		// Once we have the transporter registered, we will register the actual Swift
		// mailer instance, passing in the transport instances, which allows us to
		// override this transporter instances during fw start-up if necessary.
		$this->fw->bindings['swift.mailer'] = $this->fw->bindings->share(function ($fw)
{
			return new Swift_Mailer($fw->bindings['swift.transport']->driver());
		});
	}

	/**
	 * Register the Swift Transport instance.
	 *
	 * @return void
	 */
	protected function registerSwiftTransport()
	{
		$this->fw->bindings['swift.transport'] = $this->fw->bindings->share(function ($fw)
{
			return new TransportManager($fw);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return ['mailer', 'swift.mailer', 'swift.transport'];
	}
}
