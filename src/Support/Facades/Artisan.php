<?php

namesapce Penoaks\Support\Facades;

/**
 * @see \Penoaks\Contracts\Console\Kernel
 */
class Artisan extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'Penoaks\Contracts\Console\Kernel';
	}
}
