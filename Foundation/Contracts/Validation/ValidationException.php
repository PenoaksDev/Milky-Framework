<?php

namespace Foundation\Contracts\Validation;

use RuntimeException;
use Foundation\Contracts\Support\MessageProvider;

class ValidationException extends RuntimeException
{
	/**
	 * The message provider implementation.
	 *
	 * @var \Foundation\Contracts\Support\MessageProvider
	 */
	protected $provider;

	/**
	 * Create a new validation exception instance.
	 *
	 * @param  \Foundation\Contracts\Support\MessageProvider  $provider
	 * @return void
	 */
	public function __construct(MessageProvider $provider)
	{
		$this->provider = $provider;
	}

	/**
	 * Get the validation error message provider.
	 *
	 * @return \Foundation\Contracts\Support\MessageBag
	 */
	public function errors()
	{
		return $this->provider->getMessageBag();
	}

	/**
	 * Get the validation error message provider.
	 *
	 * @return \Foundation\Contracts\Support\MessageProvider
	 */
	public function getMessageProvider()
	{
		return $this->provider;
	}
}
