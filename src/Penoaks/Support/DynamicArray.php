<?php
namespace Penoaks\Support;

use Penoaks\Barebones\BaseArray;
use Penoaks\Bindings\Bindings;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class DynamicArray extends BaseArray
{
	/**
	 * @var array
	 */
	private $events;

	public function __construct()
	{
		$this->events = [];
	}

	/**
	 * Specify a callable to be called with specific methods
	 *
	 * @param $method
	 * @param $callback
	 */
	public function on( $method, $callback )
	{
		$this->events[$method] = $callback;
	}

	/**
	 * Internal method for calling events
	 *
	 * @param $method
	 * @param $key
	 * @param null $value
	 */
	protected function onCall( $method, $key, &$value = null )
	{
		if ( array_key_exists( $method, $this->events ) )
			Bindings::i()->call( $this->events[$method], $this->arr, $key, $value );
	}
}
