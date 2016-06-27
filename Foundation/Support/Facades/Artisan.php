<?php

namespace Foundation\Support\Facades;

/**
 * @see \Foundation\Contracts\Console\Kernel
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
		return 'Foundation\Contracts\Console\Kernel';
	}
}
