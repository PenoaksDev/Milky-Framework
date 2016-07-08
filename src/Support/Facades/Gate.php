<?php

namesapce Penoaks\Support\Facades;

/**
 * @see \Penoaks\Contracts\Auth\Access\Gate
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
		return 'Penoaks\Contracts\Auth\Access\Gate';
	}
}
