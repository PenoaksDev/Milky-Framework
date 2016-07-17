<?php

namespace Penoaks\Contracts\Support;

interface MessageProvider
{
	/**
	 * Get the messages for the instance.
	 *
	 * @return \Penoaks\Contracts\Support\MessageBag
	 */
	public function getMessageBag();
}
