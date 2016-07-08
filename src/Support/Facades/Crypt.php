<?php

namespace Penoaks\Support\Facades;

/**
 * @see \Penoaks\Encryption\Encrypter
 */
class Crypt extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'encrypter';
	}
}
