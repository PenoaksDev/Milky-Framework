<?php

namesapce Penoaks\Support\Facades;

/**
 * @see \Penoaks\Mail\Mailer
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
