<?php
namespace Foundation\Autoload;

class Func
{
	public static function str_starts_with($haystack, $needle)
	{
		return substr( $haystack, 0, strlen($needle) ) === $needle;
	}

	public static function str_ends_with($haystack, $needle)
	{
		return substr( $haystack, -strlen( $needle ) ) === $needle;
	}

	/**
	 * Convert the given string to a URL-friendly slug.
	 *
	 * @param  string  $string
	 * @return string
	 */
	public static function slugify($string)
	{
		return str_slug( str_replace( '&amp;', 'and', $string ), '-' );
	}

	public static function stacktrace()
	{
		$stack = debug_backtrace();
		$output = '';

		$stackLen = count($stack);
		for ($i = 1; $i < $stackLen; $i++)
		{
			$entry = $stack[$i];

			$func = $entry['function'] . '(';
			$argsLen = count($entry['args']);
			for ($j = 0; $j < $argsLen; $j++)
			{
				$my_entry = $entry['args'][$j];
				if (is_string($my_entry))
				{
					$func .= $my_entry;
				}
				if ($j < $argsLen - 1) $func .= ', ';
			}
			$func .= ')';

			$entry_file = 'NO_FILE';
			if (array_key_exists('file', $entry))
			{
				$entry_file = $entry['file'];
			}
			$entry_line = 'NO_LINE';
			if (array_key_exists('line', $entry))
			{
				$entry_line = $entry['line'];
			}
			$output .= $entry_file . ':' . $entry_line . ' - ' . $func . PHP_EOL;
		}
		return $output;
	}

	/**
	 * Special Function used to replace the not so great working array_merge.
	 * The built-in array_merge would only merge the first level of the array
	 * but would overwrite any sub arrays. This subroutine fixes that.
	 */
	static function array_join()
	{
		if (func_num_args() < 2)
		{
			trigger_error(__FUNCTION__ .' needs two or more array arguments', E_USER_WARNING);
			return;
		}

		$arrays = func_get_args();
		$merged = array();

		while ($arrays)
		{
			$array = array_shift($arrays);
			if (!is_array($array))
			{
				trigger_error(__FUNCTION__ .' encountered a non array argument', E_USER_WARNING);
				return;
			}
			if (!$array)
				continue;
			foreach ($array as $key => $value)
				if (is_string($key))
				if (is_array($value) && array_key_exists($key, $merged) && is_array($merged[$key]))
				$merged[$key] = call_user_func(__FUNCTION__, $merged[$key], $value);
			else
				$merged[$key] = $value;
			else
				$merged[] = $value;
		}
		return $merged;
	}
}
