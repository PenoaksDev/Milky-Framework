<?php namespace Milky\Binding\Resolvers;

use Milky\Binding\UniversalBuilder;
use Milky\Exceptions\ResolverException;
use Milky\Helpers\Str;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class ServiceResolver
{
	/**
	 * @var array
	 */
	protected $mapped = [];

	/**
	 * @var array
	 */
	protected $instances = [];

	/**
	 * @var array
	 */
	protected $alias = [];

	/**
	 * @var array
	 */
	protected $classAlias = [];

	/**
	 * @var \ReflectionClass
	 */
	private $reflection;

	public function addMap( $key, $resolve )
	{
		$this->mapped[$key] = $resolve;
	}

	public function addInstance( $key, $instance )
	{
		$this->instances[$key] = $instance;
	}

	public function addAlias( $keys, $alias )
	{
		if ( !is_string( $alias ) )
			throw new ResolverException( "Alias must be a string" );
		foreach ( is_array( $keys ) ? $keys : [$keys] as $key )
			$this->alias[$key] = $alias;
	}

	/**
	 * @param string $class
	 * @param string $alias
	 */
	public function addClassAlias( $class, $alias )
	{
		if ( !is_string( $alias ) )
			throw new ResolverException( "Alias must be a string" );
		$this->classAlias[$class] = $alias;
	}

	/**
	 * @return string
	 * @throws ResolverException
	 */
	public function key()
	{
		throw new ResolverException( "The service resolver [" . static::class . "] does not implement a key." );
	}

	/**
	 * @return \ReflectionClass
	 */
	public function reflection()
	{
		return $this->reflection ?: $this->reflection = new \ReflectionClass( static::class );
	}

	/**
	 * Called when the root key is requested.
	 * Sub-keys will be shared for further resolving, otherwise we will try for the manager/factory instance.
	 *
	 * @param string $rootKey
	 * @param string $key
	 * @return mixed
	 * @throws ResolverException
	 */
	public function resolve( $rootKey, $key = null )
	{
		if ( !$key )
		{
			if ( $result = $this->get( 'mgr' ) )
				return $result;

			if ( $result = $this->get( 'manager' ) )
				return $result;

			if ( $result = $this->get( 'factory' ) )
				return $result;
		}

		if ( !is_string( $key ) )
			throw new ResolverException( "Key must be a string" );

		if ( $result = $this->get( $key ) )
			return $result;

		throw new ResolverException( "Failed to resolve [" . $rootKey . ( empty( $key ) ? "" : "." . $key ) . "]" );
	}

	/**
	 * Called the a class needs resolving.
	 * Each registered resolver will be called for this purpose, the first to return non-false will succeed.
	 *
	 * @param string $class
	 */
	public function resolveClass( $class )
	{
		 // if ( !is_string( $class ) )
			// throw new ResolverException( "Class must be a string" );

		// Check if a class has been mapped to a key
		if ( array_key_exists( $class, $this->classAlias ) )
			try
			{
				return $this->resolve( null, $this->classAlias[$class] );
			}
			catch ( ResolverException $e )
			{
				// Ignore
			}

		// Check if the class has been initialized
		foreach ( $this->instances as $instance )
			if ( get_class( $instance ) == $class )
				return $instance;

		// TODO Other methods to check?

		return false;
	}

	/**
	 * @param string $key
	 * @return mixed
	 */
	private function get( $key, $args = [] )
	{
		// Check if an alias was set, if we still get null continue a normal check
		if ( array_key_exists( $key, $this->alias ) )
			if ( $result = $this->get( $this->alias[$key] ) )
				return $result;

		// Check for an instigated class
		if ( array_key_exists( $key, $this->instances ) )
			return $this->instances[$key];

		// Check for a mapped key
		if ( array_key_exists( $key, $this->mapped ) )
		{
			if ( is_callable( $this->mapped[$key] ) || $this->mapped[$key] instanceof \Closure )
				return UniversalBuilder::call( $this->mapped[$key], $args );
			else
				return $this->mapped[$key];
		}

		// Convert key into something a bit more resolvable.
		$key = str_replace( ['.', '-', '_', '\\', '/'], ' ', $key );
		$key = Str::camel( $key );

		$reflection = $this->reflection();

		// Check for a local property of the same name
		if ( $reflection->hasProperty( $key ) )
		{
			$property = $reflection->getProperty( $key );
			$property->setAccessible( true );

			return $property->getValue( $this );
		}

		// Check for a local method of the same name
		if ( $reflection->hasMethod( $key ) )
		{
			$method = $reflection->getMethod( $key );

			return $method->invoke( $this, UniversalBuilder::getDependencies( $method->getParameters(), $args ) );
		}

		// Fail with null as we can't find the requested key in the resolver
		return null;
	}

	/**
	 * Just in case. :D
	 *
	 * @param string $name
	 * @param array $arguments
	 * @return mixed
	 */
	public function __call( $name, $arguments )
	{
		return $this->get( $name, $arguments );
	}
}
