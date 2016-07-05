<?php
namespace Foundation\Events;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */

use Foundation\Barebones\Event;

class Runlevel implements Event
{
	CONST LOADING = 0;
	CONST INIT = 1;
	CONST BOOT = 2;
	CONST DONE = 2;

	/**
	 * @var int
	 */
	private $level = self::LOADING;

	/**
	 * @param int $level
	 * @return $this
	 */
	public function set( $level )
	{
		$this->level = $level;

		return $this;
	}

	/**
	 * @return int
	 */
	public function get()
	{
		return $this->level;
	}

	/**
	 * @return string
	 */
	public function asString()
	{
		switch( $this->level )
		{
			case -1:
				return "PREINIT";
			case 0:
				return "INITIALIZING";
			case 1:
				return "BOOTSTRAPPED";
		}
	}
}
