<?php

namespace Foundation\Contracts\Broadcasting;

interface Factory
{
	/**
	 * Get a broadcaster implementation by name.
	 *
	 * @param  string  $name
	 * @return void
	 */
	public function connection($name = null);
}
