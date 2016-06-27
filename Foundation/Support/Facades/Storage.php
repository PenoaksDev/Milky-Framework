<?php

namespace Foundation\Support\Facades;

/**
 * @see \Foundation\Filesystem\FilesystemManager
 */
class Storage extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'filesystem';
	}
}
