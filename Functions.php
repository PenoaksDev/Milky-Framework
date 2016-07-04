<?php
use Foundation\Support\Arr;
use Foundation\Support\Str;
use Foundation\Support\Collection;
use Foundation\Support\HtmlString;
use Foundation\Bindings\Bindings;
use Foundation\Support\Debug\Dumper;
use Foundation\Contracts\Bus\Dispatcher;
use Foundation\Contracts\Support\Htmlable;
use Foundation\Contracts\Auth\Access\Gate;
use Foundation\Contracts\Routing\UrlGenerator;
use Foundation\Contracts\Routing\ResponseFactory;
use Foundation\Contracts\Auth\Factory as AuthFactory;
use Foundation\Contracts\View\Factory as ViewFactory;
use Foundation\Contracts\Cookie\Factory as CookieFactory;
use Foundation\Database\Eloquent\Factory as EloquentFactory;
use Foundation\Contracts\Validation\Factory as ValidationFactory;

define( '__', DIRECTORY_SEPARATOR );
define( '__FW__', __DIR__ );
define ( "yes", true );
define ( "no", false );

if (! function_exists('append_config')) {
	/**
	 * Assign high numeric IDs to a config item to force appending.
	 *
	 * @param  array  $array
	 * @return array
	 */
	function append_config(array $array)
	{
		$start = 9999;

		foreach ($array as $key => $value) {
			if (is_numeric($key)) {
				$start++;

				$array[$start] = Arr::pull($array, $key);
			}
		}

		return $array;
	}
}

if (! function_exists('array_add')) {
	/**
	 * Add an element to an array using "dot" notation if it doesn't exist.
	 *
	 * @param  array   $array
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return array
	 */
	function array_add($array, $key, $value)
	{
		return Arr::add($array, $key, $value);
	}
}

if (! function_exists('array_build')) {
	/**
	 * Build a new array using a callback.
	 *
	 * @param  array  $array
	 * @param  callable  $callback
	 * @return array
	 *
	 * @deprecated since version 5.2.
	 */
	function array_build($array, callable $callback)
	{
		return Arr::build($array, $callback);
	}
}

if (! function_exists('array_collapse')) {
	/**
	 * Collapse an array of arrays into a single array.
	 *
	 * @param  array  $array
	 * @return array
	 */
	function array_collapse($array)
	{
		return Arr::collapse($array);
	}
}

if (! function_exists('array_divide')) {
	/**
	 * Divide an array into two arrays. One with keys and the other with values.
	 *
	 * @param  array  $array
	 * @return array
	 */
	function array_divide($array)
	{
		return Arr::divide($array);
	}
}

if (! function_exists('array_dot')) {
	/**
	 * Flatten a multi-dimensional associative array with dots.
	 *
	 * @param  array   $array
	 * @param  string  $prepend
	 * @return array
	 */
	function array_dot($array, $prepend = '')
	{
		return Arr::dot($array, $prepend);
	}
}

if (! function_exists('array_except')) {
	/**
	 * Get all of the given array except for a specified array of items.
	 *
	 * @param  array  $array
	 * @param  array|string  $keys
	 * @return array
	 */
	function array_except($array, $keys)
	{
		return Arr::except($array, $keys);
	}
}

if (! function_exists('array_first')) {
	/**
	 * Return the first element in an array passing a given truth test.
	 *
	 * @param  array  $array
	 * @param  callable|null  $callback
	 * @param  mixed  $default
	 * @return mixed
	 */
	function array_first($array, callable $callback = null, $default = null)
	{
		return Arr::first($array, $callback, $default);
	}
}

if (! function_exists('array_flatten')) {
	/**
	 * Flatten a multi-dimensional array into a single level.
	 *
	 * @param  array  $array
	 * @param  int  $depth
	 * @return array
	 */
	function array_flatten($array, $depth = INF)
	{
		return Arr::flatten($array, $depth);
	}
}

if (! function_exists('array_forget')) {
	/**
	 * Remove one or many array items from a given array using "dot" notation.
	 *
	 * @param  array  $array
	 * @param  array|string  $keys
	 * @return void
	 */
	function array_forget(&$array, $keys)
	{
		return Arr::forget($array, $keys);
	}
}

if (! function_exists('array_get')) {
	/**
	 * Get an item from an array using "dot" notation.
	 *
	 * @param  \ArrayAccess|array  $array
	 * @param  string  $key
	 * @param  mixed   $default
	 * @return mixed
	 */
	function array_get($array, $key, $default = null)
	{
		return Arr::get($array, $key, $default);
	}
}

if (! function_exists('array_has')) {
	/**
	 * Check if an item exists in an array using "dot" notation.
	 *
	 * @param  \ArrayAccess|array  $array
	 * @param  string  $key
	 * @return bool
	 */
	function array_has($array, $key)
	{
		return Arr::has($array, $key);
	}
}

if (! function_exists('array_last')) {
	/**
	 * Return the last element in an array passing a given truth test.
	 *
	 * @param  array  $array
	 * @param  callable|null  $callback
	 * @param  mixed  $default
	 * @return mixed
	 */
	function array_last($array, callable $callback = null, $default = null)
	{
		return Arr::last($array, $callback, $default);
	}
}

if (! function_exists('array_only')) {
	/**
	 * Get a subset of the items from the given array.
	 *
	 * @param  array  $array
	 * @param  array|string  $keys
	 * @return array
	 */
	function array_only($array, $keys)
	{
		return Arr::only($array, $keys);
	}
}

if (! function_exists('array_pluck')) {
	/**
	 * Pluck an array of values from an array.
	 *
	 * @param  array   $array
	 * @param  string|array  $value
	 * @param  string|array|null  $key
	 * @return array
	 */
	function array_pluck($array, $value, $key = null)
	{
		return Arr::pluck($array, $value, $key);
	}
}

if (! function_exists('array_prepend')) {
	/**
	 * Push an item onto the beginning of an array.
	 *
	 * @param  array  $array
	 * @param  mixed  $value
	 * @param  mixed  $key
	 * @return array
	 */
	function array_prepend($array, $value, $key = null)
	{
		return Arr::prepend($array, $value, $key);
	}
}

if (! function_exists('array_pull')) {
	/**
	 * Get a value from the array, and remove it.
	 *
	 * @param  array   $array
	 * @param  string  $key
	 * @param  mixed   $default
	 * @return mixed
	 */
	function array_pull(&$array, $key, $default = null)
	{
		return Arr::pull($array, $key, $default);
	}
}

if (! function_exists('array_set')) {
	/**
	 * Set an array item to a given value using "dot" notation.
	 *
	 * If no key is given to the method, the entire array will be replaced.
	 *
	 * @param  array   $array
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return array
	 */
	function array_set(&$array, $key, $value)
	{
		return Arr::set($array, $key, $value);
	}
}

if (! function_exists('array_sort')) {
	/**
	 * Sort the array using the given callback.
	 *
	 * @param  array  $array
	 * @param  callable  $callback
	 * @return array
	 */
	function array_sort($array, callable $callback)
	{
		return Arr::sort($array, $callback);
	}
}

if (! function_exists('array_sort_recursive')) {
	/**
	 * Recursively sort an array by keys and values.
	 *
	 * @param  array  $array
	 * @return array
	 */
	function array_sort_recursive($array)
	{
		return Arr::sortRecursive($array);
	}
}

if (! function_exists('array_where')) {
	/**
	 * Filter the array using the given callback.
	 *
	 * @param  array  $array
	 * @param  callable  $callback
	 * @return array
	 */
	function array_where($array, callable $callback)
	{
		return Arr::where($array, $callback);
	}
}

if (! function_exists('camel_case')) {
	/**
	 * Convert a value to camel case.
	 *
	 * @param  string  $value
	 * @return string
	 */
	function camel_case($value)
	{
		return Str::camel($value);
	}
}

if (! function_exists('class_basename')) {
	/**
	 * Get the class "basename" of the given object / class.
	 *
	 * @param  string|object  $class
	 * @return string
	 */
	function class_basename($class)
	{
		$class = is_object($class) ? get_class($class) : $class;

		return basename(str_replace('\\', '/', $class));
	}
}

if (! function_exists('class_uses_recursive')) {
	/**
	 * Returns all traits used by a class, its subclasses and trait of their traits.
	 *
	 * @param  string  $class
	 * @return array
	 */
	function class_uses_recursive($class)
	{
		$results = [];

		foreach (array_merge([$class => $class], class_parents($class)) as $class) {
			$results += trait_uses_recursive($class);
		}

		return array_unique($results);
	}
}

if (! function_exists('collect')) {
	/**
	 * Create a collection from the given value.
	 *
	 * @param  mixed  $value
	 * @return \Foundation\Support\Collection
	 */
	function collect($value = null)
	{
		return new Collection($value);
	}
}

if (! function_exists('data_fill')) {
	/**
	 * Fill in data where it's missing.
	 *
	 * @param  mixed   $target
	 * @param  string|array  $key
	 * @param  mixed  $value
	 * @return mixed
	 */
	function data_fill(&$target, $key, $value)
	{
		return data_set($target, $key, $value, false);
	}
}

if (! function_exists('data_get')) {
	/**
	 * Get an item from an array or object using "dot" notation.
	 *
	 * @param  mixed   $target
	 * @param  string|array  $key
	 * @param  mixed   $default
	 * @return mixed
	 */
	function data_get($target, $key, $default = null)
	{
		if (is_null($key)) {
			return $target;
		}

		$key = is_array($key) ? $key : explode('.', $key);

		while (($segment = array_shift($key)) !== null) {
			if ($segment === '*') {
				if ($target instanceof Collection) {
					$target = $target->all();
				} elseif (! is_array($target)) {
					return value($default);
				}

				$result = Arr::pluck($target, $key);

				return in_array('*', $key) ? Arr::collapse($result) : $result;
			}

			if (Arr::accessible($target) && Arr::exists($target, $segment)) {
				$target = $target[$segment];
			} elseif (is_object($target) && isset($target->{$segment})) {
				$target = $target->{$segment};
			} else {
				return value($default);
			}
		}

		return $target;
	}
}

if (! function_exists('data_set')) {
	/**
	 * Set an item on an array or object using dot notation.
	 *
	 * @param  mixed  $target
	 * @param  string|array  $key
	 * @param  mixed  $value
	 * @param  bool  $overwrite
	 * @return mixed
	 */
	function data_set(&$target, $key, $value, $overwrite = true)
	{
		$segments = is_array($key) ? $key : explode('.', $key);

		if (($segment = array_shift($segments)) === '*') {
			if (! Arr::accessible($target)) {
				$target = [];
			}

			if ($segments) {
				foreach ($target as &$inner) {
					data_set($inner, $segments, $value, $overwrite);
				}
			} elseif ($overwrite) {
				foreach ($target as &$inner) {
					$inner = $value;
				}
			}
		} elseif (Arr::accessible($target)) {
			if ($segments) {
				if (! Arr::exists($target, $segment)) {
					$target[$segment] = [];
				}

				data_set($target[$segment], $segments, $value, $overwrite);
			} elseif ($overwrite || ! Arr::exists($target, $segment)) {
				$target[$segment] = $value;
			}
		} elseif (is_object($target)) {
			if ($segments) {
				if (! isset($target->{$segment})) {
					$target->{$segment} = [];
				}

				data_set($target->{$segment}, $segments, $value, $overwrite);
			} elseif ($overwrite || ! isset($target->{$segment})) {
				$target->{$segment} = $value;
			}
		} else {
			$target = [];

			if ($segments) {
				data_set($target[$segment], $segments, $value, $overwrite);
			} elseif ($overwrite) {
				$target[$segment] = $value;
			}
		}

		return $target;
	}
}

if (! function_exists('dd')) {
	/**
	 * Dump the passed variables and end the script.
	 *
	 * @param  mixed
	 * @return void
	 */
	function dd()
	{
		array_map(function ($x) {
			(new Dumper)->dump($x);
		}, func_get_args());

		die(1);
	}
}

if (! function_exists('e')) {
	/**
	 * Escape HTML entities in a string.
	 *
	 * @param  \Foundation\Contracts\Support\Htmlable|string  $value
	 * @return string
	 */
	function e($value)
	{
		if ($value instanceof Htmlable) {
			return $value->toHtml();
		}

		return htmlentities($value, ENT_QUOTES, 'UTF-8', false);
	}
}

if (! function_exists('ends_with')) {
	/**
	 * Determine if a given string ends with a given substring.
	 *
	 * @param  string  $haystack
	 * @param  string|array  $needles
	 * @return bool
	 */
	function ends_with($haystack, $needles)
	{
		return Str::endsWith($haystack, $needles);
	}
}

if (! function_exists('head')) {
	/**
	 * Get the first element of an array. Useful for method chaining.
	 *
	 * @param  array  $array
	 * @return mixed
	 */
	function head($array)
	{
		return reset($array);
	}
}

if (! function_exists('last')) {
	/**
	 * Get the last element from an array.
	 *
	 * @param  array  $array
	 * @return mixed
	 */
	function last($array)
	{
		return end($array);
	}
}

if (! function_exists('object_get')) {
	/**
	 * Get an item from an object using "dot" notation.
	 *
	 * @param  object  $object
	 * @param  string  $key
	 * @param  mixed   $default
	 * @return mixed
	 */
	function object_get($object, $key, $default = null)
	{
		if (is_null($key) || trim($key) == '') {
			return $object;
		}

		foreach (explode('.', $key) as $segment) {
			if (! is_object($object) || ! isset($object->{$segment})) {
				return value($default);
			}

			$object = $object->{$segment};
		}

		return $object;
	}
}

if (! function_exists('preg_replace_sub')) {
	/**
	 * Replace a given pattern with each value in the array in sequentially.
	 *
	 * @param  string  $pattern
	 * @param  array   $replacements
	 * @param  string  $subject
	 * @return string
	 */
	function preg_replace_sub($pattern, &$replacements, $subject)
	{
		return preg_replace_callback($pattern, function ($match) use (&$replacements) {
			foreach ($replacements as $key => $value) {
				return array_shift($replacements);
			}
		}, $subject);
	}
}

if (! function_exists('snake_case')) {
	/**
	 * Convert a string to snake case.
	 *
	 * @param  string  $value
	 * @param  string  $delimiter
	 * @return string
	 */
	function snake_case($value, $delimiter = '_')
	{
		return Str::snake($value, $delimiter);
	}
}

if (! function_exists('starts_with')) {
	/**
	 * Determine if a given string starts with a given substring.
	 *
	 * @param  string  $haystack
	 * @param  string|array  $needles
	 * @return bool
	 */
	function starts_with($haystack, $needles)
	{
		return Str::startsWith($haystack, $needles);
	}
}

if (! function_exists('str_contains')) {
	/**
	 * Determine if a given string contains a given substring.
	 *
	 * @param  string  $haystack
	 * @param  string|array  $needles
	 * @return bool
	 */
	function str_contains($haystack, $needles)
	{
		return Str::contains($haystack, $needles);
	}
}

if (! function_exists('str_finish')) {
	/**
	 * Cap a string with a single instance of a given value.
	 *
	 * @param  string  $value
	 * @param  string  $cap
	 * @return string
	 */
	function str_finish($value, $cap)
	{
		return Str::finish($value, $cap);
	}
}

if (! function_exists('str_is')) {
	/**
	 * Determine if a given string matches a given pattern.
	 *
	 * @param  string  $pattern
	 * @param  string  $value
	 * @return bool
	 */
	function str_is($pattern, $value)
	{
		return Str::is($pattern, $value);
	}
}

if (! function_exists('str_limit')) {
	/**
	 * Limit the number of characters in a string.
	 *
	 * @param  string  $value
	 * @param  int	 $limit
	 * @param  string  $end
	 * @return string
	 */
	function str_limit($value, $limit = 100, $end = '...')
	{
		return Str::limit($value, $limit, $end);
	}
}

if (! function_exists('str_plural')) {
	/**
	 * Get the plural form of an English word.
	 *
	 * @param  string  $value
	 * @param  int	 $count
	 * @return string
	 */
	function str_plural($value, $count = 2)
	{
		return Str::plural($value, $count);
	}
}

if (! function_exists('str_random')) {
	/**
	 * Generate a more truly "random" alpha-numeric string.
	 *
	 * @param  int  $length
	 * @return string
	 *
	 * @throws \RuntimeException
	 */
	function str_random($length = 16)
	{
		return Str::random($length);
	}
}

if (! function_exists('str_replace_array')) {
	/**
	 * Replace a given value in the string sequentially with an array.
	 *
	 * @param  string  $search
	 * @param  array   $replace
	 * @param  string  $subject
	 * @return string
	 */
	function str_replace_array($search, array $replace, $subject)
	{
		foreach ($replace as $value) {
			$subject = preg_replace('/'.$search.'/', $value, $subject, 1);
		}

		return $subject;
	}
}

if (! function_exists('str_replace_first')) {
	/**
	 * Replace the first occurrence of a given value in the string.
	 *
	 * @param  string  $search
	 * @param  string  $replace
	 * @param  string  $subject
	 * @return string
	 */
	function str_replace_first($search, $replace, $subject)
	{
		return Str::replaceFirst($search, $replace, $subject);
	}
}

if (! function_exists('str_replace_last')) {
	/**
	 * Replace the last occurrence of a given value in the string.
	 *
	 * @param  string  $search
	 * @param  string  $replace
	 * @param  string  $subject
	 * @return string
	 */
	function str_replace_last($search, $replace, $subject)
	{
		return Str::replaceLast($search, $replace, $subject);
	}
}

if (! function_exists('str_singular')) {
	/**
	 * Get the singular form of an English word.
	 *
	 * @param  string  $value
	 * @return string
	 */
	function str_singular($value)
	{
		return Str::singular($value);
	}
}

if (! function_exists('str_slug')) {
	/**
	 * Generate a URL friendly "slug" from a given string.
	 *
	 * @param  string  $title
	 * @param  string  $separator
	 * @return string
	 */
	function str_slug($title, $separator = '-')
	{
		return Str::slug($title, $separator);
	}
}

if (! function_exists('studly_case')) {
	/**
	 * Convert a value to studly caps case.
	 *
	 * @param  string  $value
	 * @return string
	 */
	function studly_case($value)
	{
		return Str::studly($value);
	}
}

if (! function_exists('title_case')) {
	/**
	 * Convert a value to title case.
	 *
	 * @param  string  $value
	 * @return string
	 */
	function title_case($value)
	{
		return Str::title($value);
	}
}

if (! function_exists('trait_uses_recursive')) {
	/**
	 * Returns all traits used by a trait and its traits.
	 *
	 * @param  string  $trait
	 * @return array
	 */
	function trait_uses_recursive($trait)
	{
		$traits = class_uses($trait);

		foreach ($traits as $trait) {
			$traits += trait_uses_recursive($trait);
		}

		return $traits;
	}
}

if (! function_exists('value')) {
	/**
	 * Return the default value of the given value.
	 *
	 * @param  mixed  $value
	 * @return mixed
	 */
	function value($value)
	{
		return $value instanceof Closure ? $value() : $value;
	}
}

if (! function_exists('windows_os')) {
	/**
	 * Determine whether the current environment is Windows based.
	 *
	 * @return bool
	 */
	function windows_os()
	{
		return strtolower(substr(PHP_OS, 0, 3)) === 'win';
	}
}

if (! function_exists('with')) {
	/**
	 * Return the given object. Useful for chaining.
	 *
	 * @param  mixed  $object
	 * @return mixed
	 */
	function with($object)
	{
		return $object;
	}
}

if (! function_exists('abort')) {
	/**
	 * Throw an HttpException with the given data.
	 *
	 * @param  int	 $code
	 * @param  string  $message
	 * @param  array   $headers
	 * @return void
	 *
	 * @throws \Symfony\Component\HttpKernel\Exception\HttpException
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 */
	function abort($code, $message = '', array $headers = [])
	{
		return fw()->abort($code, $message, $headers);
	}
}

if (! function_exists('abort_if')) {
	/**
	 * Throw an HttpException with the given data if the given condition is true.
	 *
	 * @param  bool	$boolean
	 * @param  int	 $code
	 * @param  string  $message
	 * @param  array   $headers
	 * @return void
	 *
	 * @throws \Symfony\Component\HttpKernel\Exception\HttpException
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 */
	function abort_if($boolean, $code, $message = '', array $headers = [])
	{
		if ($boolean) {
			abort($code, $message, $headers);
		}
	}
}

if (! function_exists('abort_unless')) {
	/**
	 * Throw an HttpException with the given data unless the given condition is true.
	 *
	 * @param  bool	$boolean
	 * @param  int	 $code
	 * @param  string  $message
	 * @param  array   $headers
	 * @return void
	 *
	 * @throws \Symfony\Component\HttpKernel\Exception\HttpException
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 */
	function abort_unless($boolean, $code, $message = '', array $headers = [])
	{
		if (! $boolean) {
			abort($code, $message, $headers);
		}
	}
}

if (! function_exists('action')) {
	/**
	 * Generate a URL to a controller action.
	 *
	 * @param  string  $name
	 * @param  array   $parameters
	 * @param  bool	$absolute
	 * @return string
	 */
	function action($name, $parameters = [], $absolute = true)
	{
		return fw('url')->action($name, $parameters, $absolute);
	}
}

if (! function_exists('fw')) {
	/**
	 * Get the available bindings instance.
	 *
	 * @param  string  $make
	 * @param  array   $parameters
	 * @return mixed|\Foundation\Application
	 */
	function fw($make = null, $parameters = [])
	{
		if (is_null($make)) {
			return Bindings::getInstance();
		}

		return Bindings::getInstance()->make($make, $parameters);
	}
}

if (! function_exists('fw_path')) {
	/**
	 * Get the path to the application folder.
	 *
	 * @param  string  $path
	 * @return string
	 */
	function fw_path($path = '')
	{
		return fw('path') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
	}
}

if (! function_exists('asset')) {
	/**
	 * Generate an asset path for the application.
	 *
	 * @param  string  $path
	 * @param  bool	$secure
	 * @return string
	 */
	function asset($path, $secure = null)
	{
		return fw('url')->asset($path, $secure);
	}
}

if (! function_exists('auth')) {
	/**
	 * Get the available auth instance.
	 *
	 * @param  string|null  $guard
	 * @return \Foundation\Contracts\Auth\Factory
	 */
	function auth($guard = null)
	{
		if (is_null($guard)) {
			return fw(AuthFactory::class);
		} else {
			return fw(AuthFactory::class)->guard($guard);
		}
	}
}

if (! function_exists('back')) {
	/**
	 * Create a new redirect response to the previous location.
	 *
	 * @param  int	$status
	 * @param  array  $headers
	 * @return \Foundation\Http\RedirectResponse
	 */
	function back($status = 302, $headers = [])
	{
		return fw('redirect')->back($status, $headers);
	}
}

if (! function_exists('base_path')) {
	/**
	 * Get the path to the base of the install.
	 *
	 * @param  string  $path
	 * @return string
	 */
	function base_path($path = '')
	{
		return fw()->basePath().($path ? DIRECTORY_SEPARATOR.$path : $path);
	}
}

if (! function_exists('bcrypt')) {
	/**
	 * Hash the given value.
	 *
	 * @param  string  $value
	 * @param  array   $options
	 * @return string
	 */
	function bcrypt($value, $options = [])
	{
		return fw('hash')->make($value, $options);
	}
}

if (! function_exists('config')) {
	/**
	 * Get / set the specified configuration value.
	 *
	 * If an array is passed as the key, we will assume you want to set an array of values.
	 *
	 * @param  array|string  $key
	 * @param  mixed  $default
	 * @return mixed
	 */
	function config($key = null, $default = null)
	{
		if (is_null($key)) {
			return fw('config');
		}

		if (is_array($key)) {
			return fw('config')->set($key);
		}

		return fw('config')->get($key, $default);
	}
}

if (! function_exists('config_path')) {
	/**
	 * Get the configuration path.
	 *
	 * @param  string  $path
	 * @return string
	 */
	function config_path($path = '')
	{
		return fw()->make('path.config').($path ? DIRECTORY_SEPARATOR.$path : $path);
	}
}

if (! function_exists('cookie')) {
	/**
	 * Create a new cookie instance.
	 *
	 * @param  string  $name
	 * @param  string  $value
	 * @param  int	 $minutes
	 * @param  string  $path
	 * @param  string  $domain
	 * @param  bool	$secure
	 * @param  bool	$httpOnly
	 * @return \Symfony\Component\HttpFoundation\Cookie
	 */
	function cookie($name = null, $value = null, $minutes = 0, $path = null, $domain = null, $secure = false, $httpOnly = true)
	{
		$cookie = fw(CookieFactory::class);

		if (is_null($name)) {
			return $cookie;
		}

		return $cookie->make($name, $value, $minutes, $path, $domain, $secure, $httpOnly);
	}
}

if (! function_exists('csrf_field')) {
	/**
	 * Generate a CSRF token form field.
	 *
	 * @return \Foundation\Support\HtmlString
	 */
	function csrf_field()
	{
		return new HtmlString('<input type="hidden" name="_token" value="'.csrf_token().'">');
	}
}

if (! function_exists('csrf_token')) {
	/**
	 * Get the CSRF token value.
	 *
	 * @return string
	 *
	 * @throws \RuntimeException
	 */
	function csrf_token()
	{
		$session = fw('session');

		if (isset($session)) {
			return $session->getToken();
		}

		throw new RuntimeException('Application session store not set.');
	}
}

if (! function_exists('database_path')) {
	/**
	 * Get the database path.
	 *
	 * @param  string  $path
	 * @return string
	 */
	function database_path($path = '')
	{
		return fw()->databasePath().($path ? DIRECTORY_SEPARATOR.$path : $path);
	}
}

if (! function_exists('decrypt')) {
	/**
	 * Decrypt the given value.
	 *
	 * @param  string  $value
	 * @return string
	 */
	function decrypt($value)
	{
		return fw('encrypter')->decrypt($value);
	}
}

if (! function_exists('dispatch')) {
	/**
	 * Dispatch a job to its appropriate handler.
	 *
	 * @param  mixed  $job
	 * @return mixed
	 */
	function dispatch($job)
	{
		return fw(Dispatcher::class)->dispatch($job);
	}
}

if (! function_exists('elixir')) {
	/**
	 * Get the path to a versioned Elixir file.
	 *
	 * @param  string  $file
	 * @param  string  $buildDirectory
	 * @return string
	 *
	 * @throws \InvalidArgumentException
	 */
	function elixir($file, $buildDirectory = 'build')
	{
		static $manifest;
		static $manifestPath;

		if (is_null($manifest) || $manifestPath !== $buildDirectory) {
			$manifest = json_decode(file_get_contents(public_path($buildDirectory.'/rev-manifest.json')), true);

			$manifestPath = $buildDirectory;
		}

		if (isset($manifest[$file])) {
			return '/'.trim($buildDirectory.'/'.$manifest[$file], '/');
		}

		throw new InvalidArgumentException("File {$file} not defined in asset manifest.");
	}
}

if (! function_exists('encrypt')) {
	/**
	 * Encrypt the given value.
	 *
	 * @param  string  $value
	 * @return string
	 */
	function encrypt($value)
	{
		return fw('encrypter')->encrypt($value);
	}
}

if (! function_exists('env')) {
	/**
	 * Gets the value of an environment variable. Supports boolean, empty and null.
	 *
	 * @param  string  $key
	 * @param  mixed   $default
	 * @return mixed
	 */
	function env($key, $default = null)
	{
		$value = getenv($key);

		if ($value === false) {
			return value($default);
		}

		switch (strtolower($value)) {
			case 'true':
			case '(true)':
				return true;
			case 'false':
			case '(false)':
				return false;
			case 'empty':
			case '(empty)':
				return '';
			case 'null':
			case '(null)':
				return;
		}

		if (strlen($value) > 1 && Str::startsWith($value, '"') && Str::endsWith($value, '"')) {
			return substr($value, 1, -1);
		}

		return $value;
	}
}

if (! function_exists('event')) {
	/**
	 * Fire an event and call the listeners.
	 *
	 * @param  string|object  $event
	 * @param  mixed  $payload
	 * @param  bool  $halt
	 * @return array|null
	 */
	function event($event, $payload = [], $halt = false)
	{
		return fw('events')->fire($event, $payload, $halt);
	}
}

if (! function_exists('factory')) {
	/**
	 * Create a model factory builder for a given class, name, and amount.
	 *
	 * @param  dynamic  class|class,name|class,amount|class,name,amount
	 * @return \Foundation\Database\Eloquent\FactoryBuilder
	 */
	function factory()
	{
		$factory = fw(EloquentFactory::class);

		$arguments = func_get_args();

		if (isset($arguments[1]) && is_string($arguments[1])) {
			return $factory->of($arguments[0], $arguments[1])->times(isset($arguments[2]) ? $arguments[2] : 1);
		} elseif (isset($arguments[1])) {
			return $factory->of($arguments[0])->times($arguments[1]);
		} else {
			return $factory->of($arguments[0]);
		}
	}
}

if (! function_exists('info')) {
	/**
	 * Write some information to the log.
	 *
	 * @param  string  $message
	 * @param  array   $context
	 * @return void
	 */
	function info($message, $context = [])
	{
		return fw('log')->info($message, $context);
	}
}

if (! function_exists('logger')) {
	/**
	 * Log a debug message to the logs.
	 *
	 * @param  string  $message
	 * @param  array  $context
	 * @return \Foundation\Contracts\Logging\Log|null
	 */
	function logger($message = null, array $context = [])
	{
		if (is_null($message)) {
			return fw('log');
		}

		return fw('log')->debug($message, $context);
	}
}

if (! function_exists('method_field')) {
	/**
	 * Generate a form field to spoof the HTTP verb used by forms.
	 *
	 * @param  string  $method
	 * @return \Foundation\Support\HtmlString
	 */
	function method_field($method)
	{
		return new HtmlString('<input type="hidden" name="_method" value="'.$method.'">');
	}
}

if (! function_exists('old')) {
	/**
	 * Retrieve an old input item.
	 *
	 * @param  string  $key
	 * @param  mixed   $default
	 * @return mixed
	 */
	function old($key = null, $default = null)
	{
		return fw('request')->old($key, $default);
	}
}

if (! function_exists('policy')) {
	/**
	 * Get a policy instance for a given class.
	 *
	 * @param  object|string  $class
	 * @return mixed
	 *
	 * @throws \InvalidArgumentException
	 */
	function policy($class)
	{
		return fw(Gate::class)->getPolicyFor($class);
	}
}

if (! function_exists('public_path')) {
	/**
	 * Get the path to the public folder.
	 *
	 * @param  string  $path
	 * @return string
	 */
	function public_path($path = '')
	{
		return fw()->make('path.public').($path ? DIRECTORY_SEPARATOR.$path : $path);
	}
}

if (! function_exists('redirect')) {
	/**
	 * Get an instance of the redirector.
	 *
	 * @param  string|null  $to
	 * @param  int	 $status
	 * @param  array   $headers
	 * @param  bool	$secure
	 * @return \Foundation\Routing\Redirector|\Foundation\Http\RedirectResponse
	 */
	function redirect($to = null, $status = 302, $headers = [], $secure = null)
	{
		if (is_null($to)) {
			return fw('redirect');
		}

		return fw('redirect')->to($to, $status, $headers, $secure);
	}
}

if (! function_exists('request')) {
	/**
	 * Get an instance of the current request or an input item from the request.
	 *
	 * @param  string  $key
	 * @param  mixed   $default
	 * @return \Foundation\Http\Request|string|array
	 */
	function request($key = null, $default = null)
	{
		if (is_null($key)) {
			return fw('request');
		}

		return fw('request')->input($key, $default);
	}
}

if (! function_exists('resource_path')) {
	/**
	 * Get the path to the resources folder.
	 *
	 * @param  string  $path
	 * @return string
	 */
	function resource_path($path = '')
	{
		return fw()->basePath().DIRECTORY_SEPARATOR.'resources'.($path ? DIRECTORY_SEPARATOR.$path : $path);
	}
}

if (! function_exists('response')) {
	/**
	 * Return a new response from the application.
	 *
	 * @param  string  $content
	 * @param  int	 $status
	 * @param  array   $headers
	 * @return \Symfony\Component\HttpFoundation\Response|\Foundation\Contracts\Routing\ResponseFactory
	 */
	function response($content = '', $status = 200, array $headers = [])
	{
		$factory = fw(ResponseFactory::class);

		if (func_num_args() === 0) {
			return $factory;
		}

		return $factory->make($content, $status, $headers);
	}
}

if (! function_exists('route')) {
	/**
	 * Generate a URL to a named route.
	 *
	 * @param  string  $name
	 * @param  array   $parameters
	 * @param  bool	$absolute
	 * @return string
	 */
	function route($name, $parameters = [], $absolute = true)
	{
		return fw('url')->route($name, $parameters, $absolute);
	}
}

if (! function_exists('secure_asset')) {
	/**
	 * Generate an asset path for the application.
	 *
	 * @param  string  $path
	 * @return string
	 */
	function secure_asset($path)
	{
		return asset($path, true);
	}
}

if (! function_exists('secure_url')) {
	/**
	 * Generate a HTTPS url for the application.
	 *
	 * @param  string  $path
	 * @param  mixed   $parameters
	 * @return string
	 */
	function secure_url($path, $parameters = [])
	{
		return url($path, $parameters, true);
	}
}

if (! function_exists('session')) {
	/**
	 * Get / set the specified session value.
	 *
	 * If an array is passed as the key, we will assume you want to set an array of values.
	 *
	 * @param  array|string  $key
	 * @param  mixed  $default
	 * @return mixed
	 */
	function session($key = null, $default = null)
	{
		if (is_null($key)) {
			return fw('session');
		}

		if (is_array($key)) {
			return fw('session')->put($key);
		}

		return fw('session')->get($key, $default);
	}
}

if (! function_exists('storage_path')) {
	/**
	 * Get the path to the storage folder.
	 *
	 * @param  string  $path
	 * @return string
	 */
	function storage_path($path = '')
	{
		return fw('path.storage').($path ? DIRECTORY_SEPARATOR.$path : $path);
	}
}

if (! function_exists('trans')) {
	/**
	 * Translate the given message.
	 *
	 * @param  string  $id
	 * @param  array   $parameters
	 * @param  string  $domain
	 * @param  string  $locale
	 * @return \Symfony\Component\Translation\TranslatorInterface|string
	 */
	function trans($id = null, $parameters = [], $domain = 'messages', $locale = null)
	{
		if (is_null($id)) {
			return fw('translator');
		}

		return fw('translator')->trans($id, $parameters, $domain, $locale);
	}
}

if (! function_exists('trans_choice')) {
	/**
	 * Translates the given message based on a count.
	 *
	 * @param  string  $id
	 * @param  int|array|\Countable  $number
	 * @param  array   $parameters
	 * @param  string  $domain
	 * @param  string  $locale
	 * @return string
	 */
	function trans_choice($id, $number, array $parameters = [], $domain = 'messages', $locale = null)
	{
		return fw('translator')->transChoice($id, $number, $parameters, $domain, $locale);
	}
}

if (! function_exists('url')) {
	/**
	 * Generate a url for the application.
	 *
	 * @param  string  $path
	 * @param  mixed   $parameters
	 * @param  bool	$secure
	 * @return Foundation\Contracts\Routing\UrlGenerator|string
	 */
	function url($path = null, $parameters = [], $secure = null)
	{
		if (is_null($path)) {
			return fw(UrlGenerator::class);
		}

		return fw(UrlGenerator::class)->to($path, $parameters, $secure);
	}
}

if (! function_exists('validator')) {
	/**
	 * Create a new Validator instance.
	 *
	 * @param  array  $data
	 * @param  array  $rules
	 * @param  array  $messages
	 * @param  array  $customAttributes
	 * @return \Foundation\Contracts\Validation\Validator
	 */
	function validator(array $data = [], array $rules = [], array $messages = [], array $customAttributes = [])
	{
		$factory = fw(ValidationFactory::class);

		if (func_num_args() === 0) {
			return $factory;
		}

		return $factory->make($data, $rules, $messages, $customAttributes);
	}
}

if (! function_exists('view')) {
	/**
	 * Get the evaluated view contents for the given view.
	 *
	 * @param  string  $view
	 * @param  array   $data
	 * @param  array   $mergeData
	 * @return \Foundation\View\View|\Foundation\Contracts\View\Factory
	 */
	function view($view = null, $data = [], $mergeData = [])
	{
		$factory = fw(ViewFactory::class);

		if (func_num_args() === 0) {
			return $factory;
		}

		return $factory->make($view, $data, $mergeData);
	}
}
