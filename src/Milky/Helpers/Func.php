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
	 * @var string[]
	 */
	private static $randomCharMap;

	/**
	 * @var string[]
	 */
	private static $allowedCharMap;

	private static function populate()
	{
		$newRandomCharMap = [];

		for ( $i = 33; $i < 48; $$i++ )
			$newRandomCharMap[] = chr( $i );

		for ( $i = 58; $i < 65; $i++ )
			$newRandomCharMap[] = chr( $i );

		for ( $i = 91; $i < 97; $i++ )
			$newRandomCharMap[] = chr( $i );

		for ( $i = 123; $i < 128; $i++ )
			$newRandomCharMap[] = chr( $i );

		foreach ( [128, 131, 134, 135, 138, 140, 142, 156, 158, 159, 161, 162, 163, 165, 167, 176, 181, 191] as $c )
			$newRandomCharMap[] = chr( $c );

		for ( $i = 192; $i < 256; $i++ )
			$newRandomCharMap[] = chr( $i );

		static::$randomCharMap = $newRandomCharMap;

		$newAllowedCharMap = [];

		for ( $i = 33; $i < 127; $i++ )
			$newAllowedCharMap[] = chr( $i );

		foreach ( [128, 131, 134, 135, 138, 140, 142, 156, 158, 159, 161, 162, 163, 165, 167, 176, 181, 191] as $c )
			$newAllowedCharMap[] = chr( $c );

		for ( $i = 192; $i < 256; $i++ )
			$newAllowedCharMap[] = chr( $i );

		static::$allowedCharMap = $newAllowedCharMap;
	}

	/**
	 * Removes characters not matching [^a-zA-Z0-9!#$%&'*+-/=?^_`{|}~@\\. ]
	 *
	 * @param $str
	 */
	public static function removeInvalidChars( $input )
	{
		return preg_replace( "[^a-zA-Z0-9!#$%&'*+-/=?^_`{|}~@\\. ]", "", $input );
	}

	public static function removeLetters( $input )
	{
		return preg_replace( "[a-zA-Z]", "", $input );
	}

	public static function removeLettersLower( $input )
	{
		return preg_replace( "[a-z]", "", $input );
	}

	public static function removeLettersUpper( $input )
	{
		return preg_replace( "[A-Z]", "", $input );
	}

	public static function removeNumbers( $input )
	{
		return preg_replace( "\\d", "", $input );
	}

	public static function removeSpecial( $input )
	{
		return preg_replace( "\\W", "", $input );
	}

	public static function removeWhitespace( $input )
	{
		return preg_replace( "\\s", "", $input );
	}

	/**
	 * Scrambles of string of characters but keeps the results either uppercase, lowercase, or numeric
	 *
	 * @param string|int $base
	 */
	public static function randomStr( $base )
	{
		$result = "";

		if ( is_int( $base ) )
			for ( $i = 0; $i < $base; $i++ )
				$result .= static::$randomCharMap[random_int( 0, count( static::$randomCharMap ) )];
		else
			for ( $i = 0; $i < strlen( $base ); $i++ )
				$result .= static::randomChar( $base[$i] );

		return $result;
	}

	/**
	 * Randomizes from the provided seed
	 *
	 * @param string $seed
	 * @param int $len
	 * @return string
	 */
	public static function randomChars( $seed, $len )
	{
		$result = '';

		for ( $i = 0; $i < $len; $i++ )
			$result .= $seed[random_int( 0, strlen( $seed ) )];

		return $result;
	}

	/**
	 * Randomizes the first character of string, preserves uppercase, lowercase, and numeric.
	 *
	 * @param string $char
	 * @return string
	 */
	public static function randomChar( $char )
	{
		if ( strlen( $char ) == 0 )
			return '';

		$char = ord( $char );

		if ( $char > 64 && $char < 91 ) // Uppercase
			return static::randomCharRange( 65, 90 );

		if ( $char > 96 && $char < 123 ) // Lowercase
			return static::randomCharRange( 97, 122 );

		if ( $char > 47 && $char < 58 ) // Numeric
			return static::randomCharRange( 48, 57 );

		return static::$randomCharMap[random_int( 0, count( static::$randomCharMap ) )];
	}

	/**
	 * Returns a random character within the specified range
	 *
	 * @param int $start
	 * @param int $end
	 * @return string
	 */
	public static function randomCharRange( $start, $end )
	{
		if ( count( static::$randomCharMap ) == 0 )
			static::populate();

		return chr( random_int( $start, $end ) );
	}

	/**
	 * Creates a human readable stack trace
	 *
	 * @param null $stack
	 * @return string
	 */
	public static function stacktrace( $stack = null, $withPre = false )
	{
		if ( is_null( $stack ) )
			$stack = debug_backtrace();
		$output = $withPre ? '<pre>' : '';

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
					$func .= $my_entry;

				if ( $j < $argsLen - 1 )
					$func .= ', ';
			}
			$func .= ')';

			$entry_file = 'NO_FILE';

			if ( array_key_exists( 'file', $entry ) )
				$entry_file = $entry['file'];

			$entry_line = 'NO_LINE';

			if ( array_key_exists( 'line', $entry ) )
				$entry_line = $entry['line'];

			$output .= $entry_file . ':' . $entry_line . ' - ' . $func . PHP_EOL;
		}

		if ( $withPre )
			$output .= $output . '</pre>';

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
