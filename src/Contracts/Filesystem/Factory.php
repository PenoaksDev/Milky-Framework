<?php

namesapce Penoaks\Contracts\Filesystem;

interface Factory
{
	/**
	 * Get a filesystem implementation.
	 *
	 * @param  string  $name
	 * @return \Penoaks\Contracts\Filesystem\Filesystem
	 */
	public function disk($name = null);
}
