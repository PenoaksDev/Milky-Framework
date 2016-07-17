<?php
namespace Penoaks\Events;

use Penoaks\Barebones\Event;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class EnvMissingEvent implements Event
{
	use Traits\DynamicProperties;
	use Traits\Cancellable;

	/**
	 * EnvMissingEvent constructor.
	 *
	 * @param array $keys
	 * @param mixed|null $def
	 */
	public function __construct( array $keys, $def = null )
	{
		$this->keys = $keys;
		$this->def = $def;
	}

	/**
	 * @param mixed $def
	 */
	public function setDefault( $def )
	{
		$this->def = $def;
	}

	/**
	 * @return mixed
	 */
	public function getDefault()
	{
		return $this->def;
	}
}
