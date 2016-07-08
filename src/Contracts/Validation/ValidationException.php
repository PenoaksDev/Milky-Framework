<?php

namespace Penoaks\Contracts\Validation;

use RuntimeException;
use Penoaks\Contracts\Support\MessageProvider;

class ValidationException extends RuntimeException
{
	/**
	 * The message provider implementation.
	 *
	 * @var \Penoaks\Contracts\Support\MessageProvider
	 */
	protected $provider;

	/**
	 * Create a new validation exception instance.
	 *
	 * @param  \Penoaks\Contracts\Support\MessageProvider  $provider
	 * @return void
	 */
	public function __construct(MessageProvider $provider)
	{
		$this->provider = $provider;
	}

	/**
	 * Get the validation error message provider.
	 *
	 * @return \Penoaks\Contracts\Support\MessageBag
	 */
	public function errors()
	{
		return $this->provider->getMessageBag();
	}

	/**
	 * Get the validation error message provider.
	 *
	 * @return \Penoaks\Contracts\Support\MessageProvider
	 */
	public function getMessageProvider()
	{
		return $this->provider;
	}
}
