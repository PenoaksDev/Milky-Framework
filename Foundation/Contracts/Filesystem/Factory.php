<?php

namespace Foundation\Contracts\Filesystem;

interface Factory
{
	/**
	 * Get a filesystem implementation.
	 *
	 * @param  string  $name
	 * @return \Foundation\Contracts\Filesystem\Filesystem
	 */
	public function disk($name = null);
}
