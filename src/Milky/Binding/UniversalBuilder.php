<?php namespace Milky\Binding;

use Milky\Account\AccountServiceResolver;
use Milky\Binding\Resolvers\ServiceResolver;
use Milky\Cache\CacheManager;
use Milky\Cache\CacheServiceResolver;
use Milky\Cache\Console\ClearCommand;
use Milky\Config\Configuration;
use Milky\Console\CommandServiceResolver;
use Milky\Database\DatabaseServiceResolver;
use Milky\Exceptions\BindingException;
use Milky\Exceptions\ExceptionsServiceResolver;
use Milky\Exceptions\Handler;
use Milky\Exceptions\ResolverException;
use Milky\Framework;
use Milky\Helpers\Arr;
use Milky\Http\HttpFactory;
use Milky\Http\HttpServiceResolver;
use Milky\Http\View\ViewServiceResolver;
use Milky\Logging\Logger;
use Milky\Mail\MailerServiceResolver;
use Milky\Queue\QueueServiceResolver;
use Milky\Translation\TranslationServiceResolver;
use Milky\Validation\ValidationServiceResolver;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class UniversalBuilder
{
	/**
	 * BindingBuilder constructor.
	 *
	 * @param Framework $fw
	 */
	public function __construct( Framework $fw )
	{
		static::registerResolver( new ExceptionsServiceResolver() );

		static::registerResolver( new HttpServiceResolver() );

		static::registerResolver( new DatabaseServiceResolver() );

		static::registerResolver( new AccountServiceResolver() );

		static::registerResolver( new CommandServiceResolver() );

		static::registerResolver( new QueueServiceResolver() );

		static::registerResolver( new CacheServiceResolver() );

		static::registerResolver( new ViewServiceResolver() );

		static::registerResolver( new TranslationServiceResolver() );

		static::registerResolver( new ValidationServiceResolver() );

		static::registerResolver( new MailerServiceResolver() );

		// static::getResolver( 'command' )->cacheClear = new ClearCommand( CacheManager::i() );
	}

	/**
	 * @var array
	 */
	private static $buildStack = [];

	/**
	 * @var ServiceResolver[]
	 */
	protected static $resolvers = [];

	/**
	 * Are we building this class
	 *
	 * @param string $class
	 * @return bool
	 */
	public static function building( $class )
	{
		if ( !is_string( $class ) )
			throw new BindingException( "The \$class must be a string" );

		return in_array( $class, static::$buildStack );
	}

	/**
	 * @param ServiceResolver $resolver
	 * @param string $key
	 */
	public static function registerResolver( ServiceResolver $resolver, $key = null )
	{
		$key = $key ?: $resolver->key();
		Arr::set( static::$resolvers, $key, $resolver );
	}

	/**
	 * @param $name
	 * @return ServiceResolver
	 */
	public static function getResolver( $name )
	{
		return Arr::get( static::$resolvers, $name );
	}

	/**
	 * Attempts to locate a class within the registered resolvers.
	 * Optionally will build the class on failure.
	 *
	 * @param string $class
	 * @param bool $buildOnFailure
	 * @param array $parameters
	 * @return null|object
	 */
	public static function resolveClass( $class, $buildOnFailure = false, $parameters = [] )
	{
		if ( !is_string( $class ) )
			throw new ResolverException( "Class must be a string" );

		if ( $class == Framework::class )
			return Framework::fw();
		if ( $class == Configuration::class )
			return Framework::config();
		if ( $class == Logger::class )
			return Framework::log();

		// TODO Add a few more basic classes

		foreach ( static::$resolvers as $resolver )
			if ( false !== ( $result = $resolver->resolveClass( $class ) ) )
				return $result;

		if ( $buildOnFailure )
			return static::buildClass( $class, $parameters );

		return null;
	}

	/**
	 * Attempts to construct a class
	 *
	 * @param string $class
	 * @param array $parameters
	 *
	 * @return object
	 * @throws BindingException
	 */
	public static function buildClass( $class, array $parameters = [] )
	{
		if ( !is_string( $class ) )
			throw new BindingException( "Class must be a string" );

		if ( static::building( $class ) )
			throw new BindingException( "The class [" . $class . "] is already being built." );

		static::$buildStack[] = $class;

		try
		{
			$reflector = new \ReflectionClass( $class );

			// If the type is not instantiable, the developer is attempting to resolve
			// an abstract type such as an Interface of Abstract Class and there is
			// no binding registered for the abstractions so we need to bail out.
			if ( !$reflector->isInstantiable() )
				throw new BindingException( "Target [$class] is not instantiable." );

			$constructor = $reflector->getConstructor();

			// If there are no constructors, that means there are no dependencies then
			// we can just resolve the instances of the objects right away.
			if ( is_null( $constructor ) )
				return new $class;

			$dependencies = $constructor->getParameters();

			// Once we have all the constructor's parameters we can create each of the
			// dependency instances and then use the reflection instances to make a
			// new instance of this class, injecting the created dependencies in.
			$parameters = static::keyParametersByArgument( $dependencies, $parameters );

			$instances = static::getDependencies( $dependencies, $parameters, $class );

			array_pop( static::$buildStack );

			/**/

			return $reflector->newInstanceArgs( $instances );
		}
		catch ( \ReflectionException $e )
		{
			array_pop( static::$buildStack );

			throw new BindingException( "Failed to build [$class]: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine() );
		}
	}

	public static function resolve( $key )
	{
		if ( strpos( $key, '\\' ) !== false )
			return static::resolveClass( $key );

		$key = explode( '.', $key );

		if ( $resolver = static::getResolver( $key[0] ) )
			return $resolver->resolve( $key[0], implode( '.', array_slice( $key, 1 ) ) );

		return null;
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

			$callback = [static::resolve( $segments[0] ), $method];
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
	 * @param  \ReflectionParameter[] $parameters
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
					$dependencies[] = static::resolve( $parameter->name );
				else
				{
					$depend = static::resolveClass( $parameter->getClass()->name );
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
