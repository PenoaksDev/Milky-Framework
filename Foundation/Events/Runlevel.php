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
	CONST DONE = 3;

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

	public function __toString()
	{
		return self::asString( $this->level );
	}

	/**
	 * @param int $level
	 * @return string
	 */
	public static function asString( $level )
	{
		switch( $level )
		{
			case 0:
				return "LOADING";
			case 1:
				return "INIT";
			case 2:
				return "BOOT";
			case 3:
				return "DONE";
		}
	}
}
