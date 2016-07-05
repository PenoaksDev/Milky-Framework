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

trait DynamicProperties
{
	/**
	 * @var array
	 */
	public $values;

	/**
	 * Get property
	 *
	 * @param $key
	 * @return mixed
	 */
	public function __get( $key )
	{
		return $this->values[$key];
	}

	/**
	 * Set property
	 *
	 * @param $key
	 * @param $value
	 */
	public function __set( $key, $value )
	{
		return $this->values[$key] = $value;
	}
}
