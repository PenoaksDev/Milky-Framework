<?php
namespace Penoaks\Framework;

use Closure;
use Penoaks\Bindings\Bindings;
use Penoaks\Events\EnvMissingEvent;
use Penoaks\Support\Arr;
use Penoaks\Support\Str;

class Env implements \ArrayAccess
{
	use \Penoaks\Traits\StaticAccess;

	/**
	 * Holds environment variables
	 *
	 * @var array
	 */
	protected $variables = [];

	/**
	 * Env constructor.
	 *
	 * @param array $params
	 */
	public function __construct( array $params )
	{
		static::$selfInstance = $this;
		$this->variables = $params;
	}

	public function set( $params, $value = null )
	{
		if ( static::wasStatic() )
			static::__callStatic( __METHOD__, func_get_args() );
		else
		{
			if ( is_array( $params ) )
				$this->variables = array_merge_recursive( $this->variables, $params );
			else if ( is_null( $value ) )
				unset( $this->variables[$params] );
			else
				$this->variables[$params] = $value;
		}
	}

	/**
	 * @param $key
	 * @param null $def
	 * @return array|mixed|null
	 */
	public static function get( $key, $def = null )
	{
		$keys = explode( '.', $key );
		$value = static::i()->variables;

		foreach ( $keys as $k )
		{
			if ( !is_array( $value ) || !array_key_exists( $k, $value ) )
			{
				$value = null;
				break;
			}
			$value = $value[$k];
		}

		if ( is_null( $value ) )
		{
			$event = new EnvMissingEvent( $keys, $def );
			Bindings::get( 'events' )->fire( $event );

			return $event->isCancelled() ? $def : $event->getDefault();
		}

		return $value;
	}

	public function offsetExists( $key )
	{
		return array_key_exists( $key, $this->variables );
	}

	public function offsetGet( $key )
	{
		return $this->get( $key );
	}

	public function offsetSet( $key, $value )
	{
		$this->set( $key, $value );
	}

	public function offsetUnset( $key )
	{
		unset( $this->variables[$key] );
	}

	/**
	 * Detect the application's current environment.
	 *
	 * @param  \Closure $callback
	 * @param  array|null $consoleArgs
	 * @return string
	 */
	public function detect( Closure $callback, $consoleArgs = null )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		if ( $consoleArgs )
		{
			return $this->detectConsoleEnvironment( $callback, $consoleArgs );
		}

		return $this->detectWebEnvironment( $callback );
	}

	/**
	 * Set the application environment for a web request.
	 *
	 * @param  \Closure $callback
	 * @return string
	 */
	protected function detectWebEnvironment( Closure $callback )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		return call_user_func( $callback );
	}

	/**
	 * Set the application environment from command-line arguments.
	 *
	 * @param  \Closure $callback
	 * @param  array $args
	 * @return string
	 */
	protected function detectConsoleEnvironment( Closure $callback, array $args )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		// First we will check if an environment argument was passed via console arguments
		// and if it was that automatically overrides as the environment. Otherwise, we
		// will check the environment as a "web" request like a typical HTTP request.
		if ( !is_null( $value = $this->getEnvironmentArgument( $args ) ) )
		{
			return head( array_slice( explode( '=', $value ), 1 ) );
		}

		return $this->detectWebEnvironment( $callback );
	}

	/**
	 * Get the environment argument from the console.
	 *
	 * @param  array $args
	 * @return string|null
	 */
	protected function getEnvironmentArgument( array $args )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		return Arr::first( $args, function ( $k, $v )
		{
			return Str::startsWith( $v, '--env' );
		} );
	}
}
