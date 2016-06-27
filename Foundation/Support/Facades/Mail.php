<?php

namespace Foundation\Support\Facades;

/**
 * @see \Foundation\Mail\Mailer
 */
class Mail extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'mailer';
	}
}
