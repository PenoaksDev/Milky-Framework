<?php

namesapce Penoaks\Support\Facades;

/**
 * @see \Penoaks\Contracts\Bus\Dispatcher
 */
class Bus extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'Penoaks\Contracts\Bus\Dispatcher';
	}
}
