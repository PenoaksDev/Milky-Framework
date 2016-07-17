<?php
namespace Penoaks\Support;

class Func
{
	public static function str_starts_with( $haystack, $needle )
	{
		return substr( $haystack, 0, strlen( $needle ) ) === $needle;
	}

	public static function str_ends_with( $haystack, $needle )
	{
		return substr( $haystack, -strlen( $needle ) ) === $needle;
	}

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

	public static function stacktrace()
	{
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
