<?php namespace Milky\Exceptions\Validation;

use Milky\Helpers\MessageBag;
use Milky\Impl\MessageProvider;

class ValidationException extends \RuntimeException
{
	/**
	 * The message provider implementation.
	 *
	 * @var MessageProvider
	 */
	protected $provider;

	/**
	 * Create a new validation exception instance.
	 *
	 * @param  MessageProvider $provider
	 * @return void
	 */
	public function __construct( MessageProvider $provider )
	{
		$this->provider = $provider;
	}

	/**
	 * Get the validation error message provider.
	 *
	 * @return MessageBag
	 */
	public function errors()
	{
		return $this->provider->getMessageBag();
	}

	/**
	 * Get the validation error message provider.
	 *
	 * @return MessageProvider
	 */
	public function getMessageProvider()
	{
		return $this->provider;
	}
}
