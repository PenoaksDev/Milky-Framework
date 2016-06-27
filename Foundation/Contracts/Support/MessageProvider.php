<?php

namespace Foundation\Contracts\Support;

interface MessageProvider
{
	/**
	 * Get the messages for the instance.
	 *
	 * @return \Foundation\Contracts\Support\MessageBag
	 */
	public function getMessageBag();
}
