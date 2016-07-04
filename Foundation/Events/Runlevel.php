<?php
namespace Foundation\Events;

class Runlevel
{
	CONST PREINIT = -1;
	CONST INITIALIZING = 0;
	CONST BOOTSTRAPPED = 1;

	/**
	 * @var int
	 */
	private $level = self::PREINIT;

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
