<?php

namespace Foundation\Framework;

use Closure;

class Env implements \ArrayAccess
{
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
		$this->variables = $params;
	}

	public function set( $params, $value = null )
	{
		if ( is_array( $params ) )
		{
			$this->variables = array_merge_recursive( $this->variables, $params );
		}
		else
		{
			if ( is_null( $value ) )
			{
				unset( $this->variables[$params] );
			}
			else
			{
				$this->variables[$params] = $value;
			}
		}
	}

	public function get( $key, $def = null )
	{
		$keys = explode( '.', $key );
		$value = $this->variables;

		foreach ( $keys as $key )
		{
			if ( !is_array( $value ) || !array_key_exists( $key, $value ) )
			{
				return $def;
			}
			$value = $value[$key];
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
		return Arr::first( $args, function ( $k, $v )
		{
			return Str::startsWith( $v, '--env' );
		} );
	}
}