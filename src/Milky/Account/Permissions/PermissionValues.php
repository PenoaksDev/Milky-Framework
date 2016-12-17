<?php namespace Milky\Account\Permissions;

/**
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class PermissionValues
{
	/**
	 * True, can't be overridden by parent nodes
	 */
	const ALWAYS = "ALWAYS";

	/**
	 * True
	 */
	const YES = "YES";

	/**
	 * False
	 */
	const NO = "NO";

	/**
	 * False, can't be overridden by parent nodes
	 */
	const NEVER = "NEVER";

	const UNSET = "UNSET";

	public static function valid( &$value )
	{
		if ( $value == null || !is_string( $value ) )
			return false;

		$value = strtoupper( $value );
		return $value == self::ALWAYS || $value == self::YES || $value == self::NO || $value == self::NEVER || $value == self::UNSET;
	}
}
