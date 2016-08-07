<?php namespace Milky\Impl;

use Milky\Helpers\MessageBag;

interface MessageProvider
{
	/**
	 * Get the messages for the instance.
	 *
	 * @return MessageBag
	 */
	public function getMessageBag();
}
