<?php

namespace Penoaks\Contracts\Validation;

interface ValidatesWhenResolved
{
	/**
	 * Validate the given class instance.
	 *
	 * @return void
	 */
	public function validate();
}
