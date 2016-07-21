<?php namespace Milky\Helpers;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class Func
{
	/**
	 * Convert the given string to a URL-friendly slug.
	 *
	 * @param  string $string
	 * @return string
	 */
	public static function slugify( $string )
	{
		return str_slug( str_replace( '&amp;', 'and', $string ), '-' );
	}

	public static function stacktrace( $stack = null )
	{
		if ( is_null( $stack ) )
			$stack = debug_backtrace();
		$output = '';

		$stackLen = count( $stack );
		for ( $i = 1; $i < $stackLen; $i++ )
		{
			$entry = $stack[$i];

			$func = $entry['function'] . '(';
			$argsLen = count( $entry['args'] );
			for ( $j = 0; $j < $argsLen; $j++ )
			{
				$my_entry = $entry['args'][$j];
				if ( is_string( $my_entry ) )
				{
					$func .= $my_entry;
				}
				if ( $j < $argsLen - 1 )
					$func .= ', ';
			}
			$func .= ')';

			$entry_file = 'NO_FILE';
			if ( array_key_exists( 'file', $entry ) )
			{
				$entry_file = $entry['file'];
			}
			$entry_line = 'NO_LINE';
			if ( array_key_exists( 'line', $entry ) )
			{
				$entry_line = $entry['line'];
			}
			$output .= $entry_file . ':' . $entry_line . ' - ' . $func . PHP_EOL;
		}

		return $output;
	}

	/**
	 * Returns the last point in the backtrace
	 *
	 * @param int $hops
	 * @return string
	 */
	public static function lastHop( $hops = 1 )
	{
		$b = debug_backtrace()[$hops];

		return $b['file'] . " on line " . $b['line'];
	}
}
