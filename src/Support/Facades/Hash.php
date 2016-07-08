<?php

namespace Penoaks\Support\Facades;

/**
 * @see \Penoaks\Hashing\BcryptHasher
 */
class Hash extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'hash';
	}
}
