<?php

namespace Foundation\Support\Facades;

/**
 * @see \Foundation\Contracts\Auth\Access\Gate
 */
class Gate extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'Foundation\Contracts\Auth\Access\Gate';
	}
}
