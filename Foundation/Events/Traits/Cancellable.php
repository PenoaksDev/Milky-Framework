<?php
namespace Foundation\Events\Traits;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */

trait Cancellable
{
	/**
	 * Holes the current event state
	 *
	 * @var bool
	 */
	private $cancelled = false;

	/**
	 * Sets the current event state
	 *
	 * @param bool $cancelled
	 */
	public function cancel( $cancelled = true )
	{
		$this->cancelled = $cancelled;
	}

	/**
	 * Returns the current event state
	 *
	 * @return bool
	 */
	public function isCancelled()
	{
		return $this->cancelled;
	}
}
