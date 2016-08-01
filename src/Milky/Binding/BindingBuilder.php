<?php namespace Milky\Binding;

use Milky\Cache\Console\ClearCommand;
use Milky\Exceptions\BindingException;
use Milky\Framework;
use Milky\Http\View\Factory;
use Milky\Queue\QueueServiceProvider;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class BindingBuilder
{
	/**
	 * @var array
	 */
	private static $serviceBindingResolvers = [];

	/**
	 * BindingBuilder constructor.
	 *
	 * @param Framework $fw
	 */
	public function __construct( Framework $fw )
	{
		$fw->hooks->addHook( 'binding.failed', [$this, 'findServiceBinding'] );

		static::addServiceBindingResolver( 'command.cache.clear', function () use ( $fw )
		{
			return new ClearCommand( $fw['cache'] );
			// $this->console->addCommand( 'command.cache.clear' );
		} );

		static::addServiceBindingResolver( ['view.engine.resolver', 'blade.compiler', 'view.finder', 'blade', 'view.factory'], function () use ( $fw )
		{
			Factory::build();
		} );

		static::addServiceBindingResolver( [
			'queue.mgr',
			'queue.connection',
			'queue.listener',
			'queue.failer',
			'queue.worker'
		], function () use ( $fw )
		{
			$fw->providers->register( new QueueServiceProvider() );
		} );
	}

	/**
	 * Finds missing service bindings for use in the Framework.
	 * Because how they are handled, virtually all bindings are used on a per request basis.
	 *
	 * @param string $binding
	 */
	public function findServiceBinding( $binding )
	{
		if ( !is_string( $binding ) )
			throw new \RuntimeException( "Missing binding must be a string" );

		if ( array_key_exists( $binding, static::$serviceBindingResolvers ) )
		{
			$result = BindingBuilder::call( static::$serviceBindingResolvers[$binding] );
			if ( !is_null( $result ) && !Framework::available( $binding ) )
				Framework::set( $binding, $result );
		}
	}

	/**
	 * Adds a new service binding resolver, called when a binding is not found.
	 * The return value of the callable will be set to the binding if not already set.
	 *
	 * @param string|array $bindings
	 * @param callable $callable
	 */
	public static function addServiceBindingResolver( $bindings, callable $callable )
	{
		foreach ( is_array( $bindings ) ? $bindings : [$bindings] as $binding )
			static::$serviceBindingResolvers[$binding] = $callable;
	}

	/**
	 * Attempts to resolve a binding from a class name, key, or alias.
	 *
	 * @param $abstract
	 *
	 * @return Object
	 * @throws BindingException
	 */
	public static function resolveBinding( $abstract, array $parameters = [] )
	{
		$binding = $abstract;
		if ( Framework::available( $abstract ) || array_key_exists( $binding, static::$serviceBindingResolvers ) )
			$binding = Framework::get( $abstract );

		if ( $binding instanceof $abstract )
			return $binding;

		if ( !is_null( $obj = Framework::getByClass( $binding ) ) )
			return $obj;

		if ( is_callable( $binding ) )
			return static::call( $binding, $parameters );

		try
		{
			return static::buildBinding( $binding, $parameters );
		}
		catch ( BindingException $e )
		{
			return $binding;
		}
	}

	/**
	 * Attempts to construct a binding from a class name
	 *
	 * @param $binding
	 * @param array $parameters
	 *
	 * @return object
	 * @throws BindingException
	 */
	public static function buildBinding( $binding, array $parameters = [] )
	{
		if ( !is_string( $binding ) )
			return $binding;

		try
		{
			$reflector = new \ReflectionClass( $binding );

			// If the type is not instantiable, the developer is attempting to resolve
			// an abstract type such as an Interface of Abstract Class and there is
			// no binding registered for the abstractions so we need to bail out.
			if ( !$reflector->isInstantiable() )
				throw new BindingException( "Target [$binding] is not instantiable." );

			$constructor = $reflector->getConstructor();

			// If there are no constructors, that means there are no dependencies then
			// we can just resolve the instances of the objects right away.
			if ( is_null( $constructor ) )
				return new $binding;

			$dependencies = $constructor->getParameters();

			// Once we have all the constructor's parameters we can create each of the
			// dependency instances and then use the reflection instances to make a
			// new instance of this class, injecting the created dependencies in.
			$parameters = static::keyParametersByArgument( $dependencies, $parameters );

			$instances = static::getDependencies( $dependencies, $parameters, $binding );

			return $reflector->newInstanceArgs( $instances );
		}
		catch ( \ReflectionException $e )
		{
			throw new BindingException( "Failed to build [" . ( $binding instanceof \Closure ? static::getCallReflector( $binding )->getName() : $binding ) . "]: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine() );
		}
	}

	/**
	 * If extra parameters are passed by numeric ID, rekey them by argument name.
	 *
	 * @param  array $dependencies
	 * @param  array $parameters
	 * @return array
	 */
	protected static function keyParametersByArgument( array $dependencies, array $parameters )
	{
		foreach ( $parameters as $key => $value )
			if ( is_numeric( $key ) )
			{
				unset( $parameters[$key] );
				$parameters[$dependencies[$key]->name] = $value;
			}

		return $parameters;
	}

	/**
	 * Call the given Closure / class@method and inject its dependencies.
	 *
	 * @param  callable|string $callback
	 * @param  array $parameters
	 * @param  string|null $defaultMethod
	 *
	 * @return mixed
	 */
	public static function call( $callback, array $parameters = [], $defaultMethod = null )
	{
		if ( is_string( $callback ) && strpos( $callback, '@' ) !== false || $defaultMethod )
		{
			$segments = explode( '@', $callback );
			$method = count( $segments ) == 2 ? $segments[1] : $defaultMethod;

			if ( is_null( $method ) )
				throw new \InvalidArgumentException( 'Method not provided.' );

			$callback = [static::resolveBinding( $segments[0] ), $method];
		}

		$dependencies = static::getMethodDependencies( $callback, $parameters );

		return call_user_func_array( $callback, $dependencies );
	}

	/**
	 * Get all dependencies for a given method.
	 *
	 * @param  callable|string $callback
	 * @param  array $parameters
	 *
	 * @return array
	 */
	public static function getMethodDependencies( $callback, array $parameters = [] )
	{
		$reflector = static::getCallReflector( $callback );

		return static::getDependencies( $reflector->getParameters(), $parameters, $reflector->getName() );
	}

	/**
	 * Get the proper reflection instance for the given callback.
	 *
	 * @param  callable|string $callback
	 *
	 * @return \ReflectionFunctionAbstract
	 */
	protected static function getCallReflector( $callback )
	{
		if ( is_string( $callback ) && strpos( $callback, '::' ) !== false )
			$callback = explode( '::', $callback );

		if ( is_array( $callback ) )
			return new \ReflectionMethod( $callback[0], $callback[1] );

		return new \ReflectionFunction( $callback );
	}

	/**
	 * Resolve all of the dependencies from the ReflectionParameters.
	 *
	 * @param  array $parameters
	 * @param  array $primitives
	 * @param  string $classAndMethod
	 *
	 * @return array
	 */
	public static function getDependencies( array $parameters, array $primitives = [], $classAndMethod = null )
	{
		$dependencies = [];

		if ( !array_key_exists( 'fw', $primitives ) )
			$primitives['fw'] = Framework::fw();

		foreach ( $parameters as $parameter )
		{
			try
			{
				/*
				 * If the class is null, it means the dependency is a string or some other
				 * primitive type which we can not resolve since it is not a class and
				 * we will just bomb out with an error since we have no-where to go.
				 */
				if ( array_key_exists( $parameter->name, $primitives ) )
					$dependencies[] = $primitives[$parameter->name];
				else if ( is_null( $parameter->getClass() ) )
					$dependencies[] = static::resolveBinding( $parameter->name );
				else
				{
					$depend = static::resolveBinding( $parameter->getClass()->name );
					if ( is_array( $depend ) )
					{
						if ( array_key_exists( $parameter->name, $depend ) )
							$depend = $depend[$parameter->name];
						else
							$depend = $depend[0];
					}
					$dependencies[] = $depend;
				}
			}
			catch ( BindingException $e )
			{
				/*
				 * If we can not resolve the class instance, we will check to see if the value
				 * is optional, and if it is we will return the optional parameter value as
				 * the value of the dependency, similarly to how we do this with scalars.
				 */
				if ( $parameter->isDefaultValueAvailable() )
					$dependencies[] = $parameter->getDefaultValue();
				else if ( $parameter->isOptional() )
					$dependencies[] = null;
				else
				{
					$parameterClass = $parameter->getClass()->name;
					foreach ( $primitives as $prim )
						if ( $prim instanceof $parameterClass )
						{
							$dependencies[] = $prim;
							continue;
						}

					$params = [];
					foreach ( $parameters as $param )
						$params[] = ( !is_null( $param->getClass() ) ? $param->getClass()->getName() . " " : "" ) . ( $param->isPassedByReference() ? "&" : "" ) . "$" . $param->getName() . ( $param->isDefaultValueAvailable() ? " = " . ( is_array( $param->getDefaultValue() ) ? "[" . implode( ', ', $param->getDefaultValue() ) . "]" : $param->getDefaultValue() ) : ( $param->isOptional() ? " = null" : "" ) );
					throw new BindingException( "Dependency injection failed for " . $parameter . " on " . $classAndMethod . "( " . implode( ', ', $params ) . " )" );
				}
			}
		}

		return $dependencies;
	}
}
