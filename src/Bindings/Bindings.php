<?php
namespace Penoaks\Bindings;

use ArrayAccess;
use Closure;
use Penoaks\Contracts\Lang\BindingResolutionException;
use Penoaks\Framework;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class Bindings implements ArrayAccess
{
	use \Penoaks\Traits\StaticAccess;

	/**
	 * Stores an instance of the Framework.
	 *
	 * @var Framework
	 */
	protected static $framework;

	/**
	 * An array of the types that have been resolved.
	 *
	 * @var array
	 */
	protected $resolved = [];

	/**
	 * The bindings's bindings.
	 *
	 * @var array
	 */
	protected $bindings = [];

	/**
	 * The bindings's shared instances.
	 *
	 * @var array
	 */
	protected $instances = [];

	/**
	 * The registered type aliases.
	 *
	 * @var array
	 */
	protected $aliases = [];

	/**
	 * The extension closures for services.
	 *
	 * @var array
	 */
	protected $extenders = [];

	/**
	 * All of the registered tags.
	 *
	 * @var array
	 */
	protected $tags = [];

	/**
	 * The stack of concretions currently being built.
	 *
	 * @var array
	 */
	protected $buildStack = [];

	/**
	 * The contextual binding map.
	 *
	 * @var array
	 */
	public $contextual = [];

	/**
	 * All of the registered rebound callbacks.
	 *
	 * @var array
	 */
	protected $reboundCallbacks = [];

	/**
	 * All of the global resolving callbacks.
	 *
	 * @var array
	 */
	protected $globalResolvingCallbacks = [];

	/**
	 * All of the global after resolving callbacks.
	 *
	 * @var array
	 */
	protected $globalAfterResolvingCallbacks = [];

	/**
	 * All of the resolving callbacks by class type.
	 *
	 * @var array
	 */
	protected $resolvingCallbacks = [];

	/**
	 * All of the after resolving callbacks by class type.
	 *
	 * @var array
	 */
	protected $afterResolvingCallbacks = [];

	public function __construct( Framework $framework )
	{
		static::$selfInstance = $this;
		static::$framework = $framework;

		$this->instance( 'bindings', $this );
		$this->instance( 'fw', $framework );
	}

	/**
	 * Define a contextual binding.
	 *
	 * @param  string $concrete
	 * @return ContextualBindingBuilder
	 */
	public function when( $concrete )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		$concrete = $this->normalize( $concrete );

		return new ContextualBindingBuilder( $this, $concrete );
	}

	/**
	 * Determine if the given abstract type has been bound.
	 *
	 * @param  string $abstract
	 * @return bool
	 */
	public function bound( $abstract )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		if ( static::$framework->bound( $abstract ) )
			return true;

		$abstract = $this->normalize( $abstract );

		return isset( $this->bindings[$abstract] ) || isset( $this->instances[$abstract] ) || $this->isAlias( $abstract );
	}

	/**
	 * Determine if the given abstract type has been resolved.
	 *
	 * @param  string $abstract
	 * @return bool
	 */
	public function resolved( $abstract )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		$abstract = $this->normalize( $abstract );

		if ( $this->isAlias( $abstract ) )
			$abstract = $this->getAlias( $abstract );

		return isset( $this->resolved[$abstract] ) || isset( $this->instances[$abstract] );
	}

	/**
	 * Determine if a given string is an alias.
	 *
	 * @param  string $name
	 * @return bool
	 */
	public function isAlias( $name )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		return isset( $this->aliases[$this->normalize( $name )] );
	}

	/**
	 * Register a binding with the bindings.
	 *
	 * @param  string|array $abstract
	 * @param  \Closure|string|null $concrete
	 * @param  bool $shared
	 */
	public function bind( $abstract, $concrete = null, $shared = false )
	{
		if ( static::wasStatic() )
			static::__callStatic( __METHOD__, func_get_args() );
		else
		{
			$abstract = $this->normalize( $abstract );

			$concrete = $this->normalize( $concrete );

			// If the given types are actually an array, we will assume an alias is being
			// defined and will grab this "real" abstract class name and register this
			// alias with the bindings so that it can be used as a shortcut for it.
			if ( is_array( $abstract ) )
			{
				list( $abstract, $alias ) = $this->extractAlias( $abstract );

				$this->alias( $abstract, $alias );
			}

			// If no concrete type was given, we will simply set the concrete type to the
			// abstract type. This will allow concrete type to be registered as shared
			// without being forced to state their classes in both of the parameter.
			$this->dropStaleInstances( $abstract );

			if ( is_null( $concrete ) )
			{
				$concrete = $abstract;
			}

			// If the factory is not a Closure, it means it is just a class name which is
			// bound into this bindings to the abstract type and we will just wrap it
			// up inside its own Closure to give us more convenience when extending.
			if ( !$concrete instanceof Closure )
			{
				$concrete = $this->getClosure( $abstract, $concrete );
			}

			$this->bindings[$abstract] = compact( 'concrete', 'shared' );

			// If the abstract type was already resolved in this bindings we'll fire the
			// rebound listener so that any objects which have already gotten resolved
			// can have their copy of the object updated via the listener callbacks.
			if ( $this->resolved( $abstract ) )
			{
				$this->rebound( $abstract );
			}
		}
	}

	/**
	 * Get the Closure to be used when building a type.
	 *
	 * @param  string $abstract
	 * @param  string $concrete
	 * @return \Closure
	 */
	protected function getClosure( $abstract, $concrete )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		return function ( $c, $parameters = [] ) use ( $abstract, $concrete )
		{
			$method = ( $abstract == $concrete ) ? 'build' : 'make';

			return $c->$method( $concrete, $parameters );
		};
	}

	/**
	 * Add a contextual binding to the bindings.
	 *
	 * @param  string $concrete
	 * @param  string $abstract
	 * @param  \Closure|string $implementation
	 */
	public function addContextualBinding( $concrete, $abstract, $implementation )
	{
		if ( static::wasStatic() )
			static::__callStatic( __METHOD__, func_get_args() );
		else
			$this->contextual[$this->normalize( $concrete )][$this->normalize( $abstract )] = $this->normalize( $implementation );
	}

	/**
	 * Register a binding if it hasn't already been registered.
	 *
	 * @param  string $abstract
	 * @param  \Closure|string|null $concrete
	 * @param  bool $shared
	 */
	public function bindIf( $abstract, $concrete = null, $shared = false )
	{
		if ( static::wasStatic() )
			static::__callStatic( __METHOD__, func_get_args() );
		else if ( !$this->bound( $abstract ) )
		{
			$this->bind( $abstract, $concrete, $shared );
		}
	}

	/**
	 * Register a shared binding in the bindings.
	 *
	 * @param  string|array $abstract
	 * @param  \Closure|string|null $concrete
	 */
	public function singleton( $abstract, $concrete = null )
	{
		if ( static::wasStatic() )
			static::__callStatic( __METHOD__, func_get_args() );
		else
			$this->bind( $abstract, $concrete, true );
	}

	/**
	 * Wrap a Closure such that it is shared.
	 *
	 * @param  \Closure $closure
	 * @return \Closure
	 */
	public function share( Closure $closure )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );
		else
			return function ( $bindings ) use ( $closure )
			{
				// We'll simply declare a static variable within the Closures and if it has
				// not been set we will execute the given Closures to resolve this value
				// and return it back to these consumers of the method as an instance.
				static $object;

				if ( is_null( $object ) )
				{
					$object = $closure( $bindings );
				}

				return $object;
			};
	}

	/**
	 * "Extend" an abstract type in the bindings.
	 *
	 * @param  string $abstract
	 * @param  \Closure $closure
	 *
	 * @throws \InvalidArgumentException
	 */
	public function extend( $abstract, Closure $closure )
	{
		if ( static::wasStatic() )
			static::__callStatic( __METHOD__, func_get_args() );
		else
		{
			$abstract = $this->normalize( $abstract );

			if ( isset( $this->instances[$abstract] ) )
			{
				$this->instances[$abstract] = $closure( $this->instances[$abstract], $this );

				$this->rebound( $abstract );
			}
			else
			{
				$this->extenders[$abstract][] = $closure;
			}
		}
	}

	/**
	 * Register an existing instance as shared in the bindings.
	 *
	 * @param  string $abstract
	 * @param  mixed $instance
	 * @return $instance
	 */
	public function instance( $abstract, $instance )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		$abstract = $this->normalize( $abstract );

		// First, we will extract the alias from the abstract if it is an array so we
		// are using the correct name when binding the type. If we get an alias it
		// will be registered with the bindings so we can resolve it out later.
		if ( is_array( $abstract ) )
		{
			list( $abstract, $alias ) = $this->extractAlias( $abstract );

			$this->alias( $abstract, $alias );
		}

		unset( $this->aliases[$abstract] );

		// We'll check to determine if this type has been bound before, and if it has
		// we will fire the rebound callbacks registered with the bindings and it
		// can be updated with consuming classes that have gotten resolved here.
		$bound = $this->bound( $abstract );

		$this->instances[$abstract] = $instance;

		if ( $bound )
		{
			$this->rebound( $abstract );
		}

		return $instance;
	}

	/**
	 * Assign a set of tags to a given binding.
	 *
	 * @param  array|string $abstracts
	 * @param  array|mixed ...$tags
	 */
	public function tag( $abstracts, $tags )
	{
		if ( static::wasStatic() )
			static::__callStatic( __METHOD__, func_get_args() );
		else
		{
			$tags = is_array( $tags ) ? $tags : array_slice( func_get_args(), 1 );

			foreach ( $tags as $tag )
			{
				if ( !isset( $this->tags[$tag] ) )
				{
					$this->tags[$tag] = [];
				}

				foreach ( (array) $abstracts as $abstract )
				{
					$this->tags[$tag][] = $this->normalize( $abstract );
				}
			}
		}
	}

	/**
	 * Resolve all of the bindings for a given tag.
	 *
	 * @param  string $tag
	 * @return array
	 */
	public function tagged( $tag )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		$results = [];

		if ( isset( $this->tags[$tag] ) )
		{
			foreach ( $this->tags[$tag] as $abstract )
			{
				$results[] = $this->make( $abstract );
			}
		}

		return $results;
	}

	/**
	 * Alias a type to a different name.
	 *
	 * @param  string $abstract
	 * @param  string $alias
	 */
	public function alias( $abstract, $alias )
	{
		if ( static::wasStatic() )
			static::__callStatic( __METHOD__, func_get_args() );
		else
			$this->aliases[$alias] = $this->normalize( $abstract );
	}

	/**
	 * Extract the type and alias from a given definition.
	 *
	 * @param  array $definition
	 * @return array
	 */
	protected function extractAlias( array $definition )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		return [key( $definition ), current( $definition )];
	}

	/**
	 * Bind a new callback to an abstract's rebind event.
	 *
	 * @param  string $abstract
	 * @param  \Closure $callback
	 * @return mixed
	 */
	public function rebinding( $abstract, Closure $callback )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		$this->reboundCallbacks[$this->normalize( $abstract )][] = $callback;

		if ( $this->bound( $abstract ) )
		{
			return $this->make( $abstract );
		}
	}

	/**
	 * Refresh an instance on the given target and method.
	 *
	 * @param  string $abstract
	 * @param  mixed $target
	 * @param  string $method
	 * @return mixed
	 */
	public function refresh( $abstract, $target, $method )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		return $this->rebinding( $this->normalize( $abstract ), function ( $fw, $instance ) use ( $target, $method )
		{
			$target->{$method}( $instance );
		} );
	}

	/**
	 * Fire the "rebound" callbacks for the given abstract type.
	 *
	 * @param  string $abstract
	 */
	protected function rebound( $abstract )
	{
		if ( static::wasStatic() )
			static::__callStatic( __METHOD__, func_get_args() );
		else
		{
			$instance = $this->make( $abstract );

			foreach ( $this->getReboundCallbacks( $abstract ) as $callback )
			{
				call_user_func( $callback, $this, $instance );
			}
		}
	}

	/**
	 * Get the rebound callbacks for a given type.
	 *
	 * @param  string $abstract
	 * @return array
	 */
	protected function getReboundCallbacks( $abstract )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		if ( isset( $this->reboundCallbacks[$abstract] ) )
		{
			return $this->reboundCallbacks[$abstract];
		}

		return [];
	}

	/**
	 * Wrap the given closure such that its dependencies will be injected when executed.
	 *
	 * @param  \Closure $callback
	 * @param  array $parameters
	 * @return \Closure
	 */
	public function wrap( Closure $callback, array $parameters = [] )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		return function () use ( $callback, $parameters )
		{
			return $this->call( $callback, $parameters );
		};
	}

	/**
	 * Call the given Closure / class@method and inject its dependencies.
	 *
	 * @param  callable|string $callback
	 * @param  array $parameters
	 * @param  string|null $defaultMethod
	 * @return mixed
	 */
	public function call( $callback, array $parameters = [], $defaultMethod = null )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		if ( $this->isCallableWithAtSign( $callback ) || $defaultMethod )
			return $this->callClass( $callback, $parameters, $defaultMethod );

		$dependencies = $this->getMethodDependencies( $callback, $parameters );

		return call_user_func_array( $callback, $dependencies );
	}

	/**
	 * Determine if the given string is in Class@method syntax.
	 *
	 * @param  mixed $callback
	 * @return bool
	 */
	protected function isCallableWithAtSign( $callback )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		return is_string( $callback ) && strpos( $callback, '@' ) !== false;
	}

	/**
	 * Get all dependencies for a given method.
	 *
	 * @param  callable|string $callback
	 * @param  array $parameters
	 * @return array
	 */
	protected function getMethodDependencies( $callback, array $parameters = [] )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		$dependencies = [];

		foreach ( $this->getCallReflector( $callback )->getParameters() as $parameter )
			$this->addDependencyForCallParameter( $parameter, $parameters, $dependencies );

		return array_merge( $dependencies, $parameters );
	}

	/**
	 * Get the proper reflection instance for the given callback.
	 *
	 * @param  callable|string $callback
	 * @return \ReflectionFunctionAbstract
	 */
	protected function getCallReflector( $callback )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		if ( is_string( $callback ) && strpos( $callback, '::' ) !== false )
			$callback = explode( '::', $callback );

		if ( is_array( $callback ) )
			return new ReflectionMethod( $callback[0], $callback[1] );

		return new ReflectionFunction( $callback );
	}

	/**
	 * Get the dependency for the given call parameter.
	 *
	 * @param  \ReflectionParameter $parameter
	 * @param  array $parameters
	 * @param  array $dependencies
	 * @return mixed
	 */
	protected function addDependencyForCallParameter( ReflectionParameter $parameter, array &$parameters, &$dependencies )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		if ( array_key_exists( $parameter->name, $parameters ) )
		{
			$dependencies[] = $parameters[$parameter->name];
			unset( $parameters[$parameter->name] );
		}
		elseif ( $parameter->getClass() )
			$dependencies[] = $this->make( $parameter->getClass()->name );
		elseif ( $parameter->isDefaultValueAvailable() )
			$dependencies[] = $parameter->getDefaultValue();
	}

	/**
	 * Call a string reference to a class using Class@method syntax.
	 *
	 * @param  string $target
	 * @param  array $parameters
	 * @param  string|null $defaultMethod
	 * @return mixed
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function callClass( $target, array $parameters = [], $defaultMethod = null )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		if ( !is_string( $target ) )
			throw new \RuntimeException( "Target must be a string" );

		$segments = explode( '@', $target );

		// If the listener has an @ sign, we will assume it is being used to delimit
		// the class name from the handle method name. This allows for handlers
		// to run multiple handler methods in a single class for convenience.
		$method = count( $segments ) == 2 ? $segments[1] : $defaultMethod;

		if ( is_null( $method ) )
			throw new InvalidArgumentException( 'Method not provided.' );

		return $this->call( [$this->make( $segments[0] ), $method], $parameters );
	}

	/**
	 * Resolve the given type from the bindings.
	 *
	 * @param  string $abstract
	 * @param  array $parameters
	 * @return mixed
	 * @throws BindingResolutionException
	 */
	public function make( $abstract, array $parameters = [] )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		try
		{
			$abstract = $this->getAlias( $this->normalize( $abstract ) );

			static::$framework->make( $abstract );

			// If an instance of the type is currently being managed as a singleton we'll
			// just return an existing instance instead of instantiating new instances
			// so the developer can keep using the same objects instance every time.
			if ( isset( $this->instances[$abstract] ) )
			{
				return $this->instances[$abstract];
			}

			$concrete = $this->getConcrete( $abstract );

			// We're ready to instantiate an instance of the concrete type registered for
			// the binding. This will instantiate the types, as well as resolve any of
			// its "nested" dependencies recursively until all have gotten resolved.
			if ( $this->isBuildable( $concrete, $abstract ) )
			{
				$object = $this->build( $concrete, $parameters );
			}
			else
			{
				$object = $this->make( $concrete, $parameters );
			}

			// If we defined any extenders for this type, we'll need to spin through them
			// and apply them to the object being built. This allows for the extension
			// of services, such as changing configuration or decorating the object.
			foreach ( $this->getExtenders( $abstract ) as $extender )
			{
				$object = $extender( $object, $this );
			}

			// If the requested type is registered as a singleton we'll want to cache off
			// the instances in "memory" so we can return it later without creating an
			// entirely new instance of an object on each subsequent request for it.
			if ( $this->isShared( $abstract ) )
			{
				$this->instances[$abstract] = $object;
			}

			$this->fireResolvingCallbacks( $abstract, $object );

			$this->resolved[$abstract] = true;

			return $object;
		}
		catch ( \ReflectionException $e )
		{
			throw new BindingResolutionException( "Failed to make [" . $abstract . "]: " . $e->getMessage() );
		}
	}

	/**
	 * Get the concrete type for a given abstract.
	 *
	 * @param  string $abstract
	 * @return mixed   $concrete
	 */
	protected function getConcrete( $abstract )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		if ( !is_null( $concrete = $this->getContextualConcrete( $abstract ) ) )
			return $concrete;

		// If we don't have a registered resolver or concrete for the type, we'll just
		// assume each type is a concrete name and will attempt to resolve it as is
		// since the bindings should be able to resolve concretes automatically.
		if ( !isset( $this->bindings[$abstract] ) )
			return $abstract;

		return $this->bindings[$abstract]['concrete'];
	}

	/**
	 * Get the contextual concrete binding for the given abstract.
	 *
	 * @param  string $abstract
	 * @return string|null
	 */
	protected function getContextualConcrete( $abstract )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		if ( isset( $this->contextual[end( $this->buildStack )][$abstract] ) )
			return $this->contextual[end( $this->buildStack )][$abstract];
	}

	/**
	 * Normalize the given class name by removing leading slashes.
	 *
	 * @param  mixed $service
	 * @return mixed
	 */
	protected function normalize( $service )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		return is_string( $service ) ? ltrim( $service, '\\' ) : $service;
	}

	/**
	 * Get the extender callbacks for a given type.
	 *
	 * @param  string $abstract
	 * @return array
	 */
	protected function getExtenders( $abstract )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		if ( isset( $this->extenders[$abstract] ) )
			return $this->extenders[$abstract];

		return [];
	}

	/**
	 * Instantiate a concrete instance of the given type.
	 *
	 * @param  string $concrete
	 * @param  array $parameters
	 * @return mixed
	 *
	 * @throws BindingResolutionException
	 */
	public function build( $concrete, array $parameters = [] )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		try
		{
			// If the concrete type is actually a Closure, we will just execute it and
			// hand back the results of the functions, which allows functions to be
			// used as resolvers for more fine-tuned resolution of these objects.
			if ( $concrete instanceof Closure )
			{
				return $concrete( $this, $parameters );
			}

			$reflector = new ReflectionClass( $concrete );

			// If the type is not instantiable, the developer is attempting to resolve
			// an abstract type such as an Interface of Abstract Class and there is
			// no binding registered for the abstractions so we need to bail out.
			if ( !$reflector->isInstantiable() )
			{
				if ( !empty( $this->buildStack ) )
				{
					$previous = implode( ', ', $this->buildStack );
					$message = "Target [$concrete] is not instantiable while building [$previous].";
				}
				else
				{
					$message = "Target [$concrete] is not instantiable.";
				}

				throw new BindingResolutionException( $message );
			}

			$this->buildStack[] = $concrete;

			$constructor = $reflector->getConstructor();

			// If there are no constructors, that means there are no dependencies then
			// we can just resolve the instances of the objects right away, without
			// resolving any other types or dependencies out of these bindingss.
			if ( is_null( $constructor ) )
			{
				array_pop( $this->buildStack );
				return new $concrete;
			}

			$dependencies = $constructor->getParameters();

			// Once we have all the constructor's parameters we can create each of the
			// dependency instances and then use the reflection instances to make a
			// new instance of this class, injecting the created dependencies in.
			$parameters = $this->keyParametersByArgument( $dependencies, $parameters );

			$instances = $this->getDependencies( $dependencies, $parameters, $concrete );

			array_pop( $this->buildStack );

			return $reflector->newInstanceArgs( $instances );
		}
		catch ( \ReflectionException $e )
		{
			throw new BindingResolutionException( "Failed to build [" . $concrete . "]: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine() );
		}
	}

	/**
	 * Resolve all of the dependencies from the ReflectionParameters.
	 *
	 * @param  array $parameters
	 * @param  array $primitives
	 * @return array
	 */
	public function getDependencies( array $parameters, array $primitives = [], $classAndMethod = null )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		$dependencies = [];

		foreach ( $parameters as $parameter )
		{
			try
			{
				$dependency = $parameter->getClass();

				// If the class is null, it means the dependency is a string or some other
				// primitive type which we can not resolve since it is not a class and
				// we will just bomb out with an error since we have no-where to go.
				if ( array_key_exists( $parameter->name, $primitives ) )
				{
					$dependencies[] = $primitives[$parameter->name];
				}
				elseif ( is_null( $dependency ) )
				{
					$dependencies[] = $this->resolveNonClass( $parameter );
				}
				else
				{
					$dependencies[] = $this->make( $parameter->getClass()->name );
				}
			}
			catch ( BindingResolutionException $e )
			{
				if ( $parameter->isOptional() )
				{
					/*
					 * If we can not resolve the class instance, we will check to see if the value
					 * is optional, and if it is we will return the optional parameter value as
					 * the value of the dependency, similarly to how we do this with scalars.
					 */
					$dependencies[] = $parameter->getDefaultValue();
				}
				else
				{
					$params = [];
					foreach ( $parameters as $param )
					{
						$params[] = ( !is_null( $param->getClass() ) ? $param->getClass()->getName() . " " : "" ) . ( $param->isPassedByReference() ? "&" : "" ) . "$" . $param->getName() . ( $param->isOptional() ? " = " . $param->getDefaultValue() : "" );
					}
					throw new BindingResolutionException( "Dependency injection failed for " . $parameter . " on " . $classAndMethod . "( " . implode( ', ', $params ) . " )" );
				}
			}
		}

		return $dependencies;
	}

	/**
	 * Resolve a non-class hinted dependency.
	 *
	 * @param  \ReflectionParameter $parameter
	 * @return mixed
	 *
	 * @throws BindingResolutionException
	 */
	protected function resolveNonClass( ReflectionParameter $parameter )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		if ( !is_null( $concrete = $this->getContextualConcrete( '$' . $parameter->name ) ) )
		{
			if ( $concrete instanceof Closure )
			{
				return call_user_func( $concrete, $this );
			}
			else
			{
				return $concrete;
			}
		}

		if ( $parameter->isDefaultValueAvailable() )
		{
			return $parameter->getDefaultValue();
		}

		$message = "Unresolvable dependency resolving [$parameter] in class {$parameter->getDeclaringClass()->getName()}";

		throw new BindingResolutionException( $message );
	}

	/**
	 * If extra parameters are passed by numeric ID, rekey them by argument name.
	 *
	 * @param  array $dependencies
	 * @param  array $parameters
	 * @return array
	 */
	public function keyParametersByArgument( array $dependencies, array $parameters )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		foreach ( $parameters as $key => $value )
		{
			if ( is_numeric( $key ) )
			{
				unset( $parameters[$key] );
				$parameters[$dependencies[$key]->name] = $value;
			}
		}

		return $parameters;
	}

	/**
	 * Register a new resolving callback.
	 *
	 * @param  string $abstract
	 * @param  \Closure|null $callback
	 */
	public function resolving( $abstract, Closure $callback = null )
	{
		if ( static::wasStatic() )
			static::__callStatic( __METHOD__, func_get_args() );
		else
		{
			if ( $callback === null && $abstract instanceof Closure )
				$this->resolvingCallback( $abstract );
			else
				$this->resolvingCallbacks[$this->normalize( $abstract )][] = $callback;
		}
	}

	/**
	 * Register a new after resolving callback for all types.
	 *
	 * @param  string $abstract
	 * @param  \Closure|null $callback
	 */
	public function afterResolving( $abstract, Closure $callback = null )
	{
		if ( static::wasStatic() )
			static::__callStatic( __METHOD__, func_get_args() );
		else
		{
			if ( $abstract instanceof Closure && $callback === null )
				$this->afterResolvingCallback( $abstract );
			else
				$this->afterResolvingCallbacks[$this->normalize( $abstract )][] = $callback;
		}
	}

	/**
	 * Register a new resolving callback by type of its first argument.
	 *
	 * @param  \Closure $callback
	 */
	protected function resolvingCallback( Closure $callback )
	{
		if ( static::wasStatic() )
			static::__callStatic( __METHOD__, func_get_args() );
		else
		{
			$abstract = $this->getFunctionHint( $callback );

			if ( $abstract )
				$this->resolvingCallbacks[$abstract][] = $callback;
			else
				$this->globalResolvingCallbacks[] = $callback;
		}
	}

	/**
	 * Register a new after resolving callback by type of its first argument.
	 *
	 * @param  \Closure $callback
	 */
	protected function afterResolvingCallback( Closure $callback )
	{
		if ( static::wasStatic() )
			static::__callStatic( __METHOD__, func_get_args() );
		else
		{
			$abstract = $this->getFunctionHint( $callback );

			if ( $abstract )
			{
				$this->afterResolvingCallbacks[$abstract][] = $callback;
			}
			else
			{
				$this->globalAfterResolvingCallbacks[] = $callback;
			}
		}
	}

	/**
	 * Get the type hint for this closure's first argument.
	 *
	 * @param  \Closure $callback
	 * @return mixed
	 */
	protected function getFunctionHint( Closure $callback )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		$function = new ReflectionFunction( $callback );

		if ( $function->getNumberOfParameters() == 0 )
		{
			return;
		}

		$expected = $function->getParameters()[0];

		if ( !$expected->getClass() )
		{
			return;
		}

		return $expected->getClass()->name;
	}

	/**
	 * Fire all of the resolving callbacks.
	 *
	 * @param  string $abstract
	 * @param  mixed $object
	 */
	protected function fireResolvingCallbacks( $abstract, $object )
	{
		if ( static::wasStatic() )
			static::__callStatic( __METHOD__, func_get_args() );
		else
		{
			$this->fireCallbackArray( $object, $this->globalResolvingCallbacks );
			$this->fireCallbackArray( $object, $this->getCallbacksForType( $abstract, $object, $this->resolvingCallbacks ) );
			$this->fireCallbackArray( $object, $this->globalAfterResolvingCallbacks );
			$this->fireCallbackArray( $object, $this->getCallbacksForType( $abstract, $object, $this->afterResolvingCallbacks ) );
		}
	}

	/**
	 * Get all callbacks for a given type.
	 *
	 * @param  string $abstract
	 * @param  object $object
	 * @param  array $callbacksPerType
	 *
	 * @return array
	 */
	protected function getCallbacksForType( $abstract, $object, array $callbacksPerType )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		$results = [];

		foreach ( $callbacksPerType as $type => $callbacks )
		{
			if ( $type === $abstract || $object instanceof $type )
			{
				$results = array_merge( $results, $callbacks );
			}
		}

		return $results;
	}

	/**
	 * Fire an array of callbacks with an object.
	 *
	 * @param  mixed $object
	 * @param  array $callbacks
	 */
	protected function fireCallbackArray( $object, array $callbacks )
	{
		if ( static::wasStatic() )
			static::__callStatic( __METHOD__, func_get_args() );
		else
			foreach ( $callbacks as $callback )
			{
				$callback( $object, $this );
			}
	}

	/**
	 * Determine if a given type is shared.
	 *
	 * @param  string $abstract
	 * @return bool
	 */
	public function isShared( $abstract )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		$abstract = $this->normalize( $abstract );

		if ( isset( $this->instances[$abstract] ) )
		{
			return true;
		}

		if ( !isset( $this->bindings[$abstract]['shared'] ) )
		{
			return false;
		}

		return $this->bindings[$abstract]['shared'] === true;
	}

	/**
	 * Determine if the given concrete is buildable.
	 *
	 * @param  mixed $concrete
	 * @param  string $abstract
	 * @return bool
	 */
	protected function isBuildable( $concrete, $abstract )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		return $concrete === $abstract || $concrete instanceof Closure;
	}

	/**
	 * Get the alias for an abstract if available.
	 *
	 * @param  string $abstract
	 * @return string
	 */
	public function getAlias( $abstract )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		if ( !isset( $this->aliases[$abstract] ) )
			return $abstract;

		return $this->getAlias( $this->aliases[$abstract] );
	}

	/**
	 * Get the bindings's bindings.
	 *
	 * @return array
	 */
	public function getBindings()
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		return $this->bindings;
	}

	/**
	 * Drop all of the stale instances and aliases.
	 *
	 * @param  string $abstract
	 */
	protected function dropStaleInstances( $abstract )
	{
		if ( static::wasStatic() )
			static::__callStatic( __METHOD__, func_get_args() );
		else
			unset( $this->instances[$abstract], $this->aliases[$abstract] );
	}

	/**
	 * Remove a resolved instance from the instance cache.
	 *
	 * @param  string $abstract
	 */
	public function forgetInstance( $abstract )
	{
		if ( static::wasStatic() )
			static::__callStatic( __METHOD__, func_get_args() );
		else
			unset( $this->instances[$this->normalize( $abstract )] );
	}

	/**
	 * Clear all of the instances from the bindings.
	 */
	public function forgetInstances()
	{
		if ( static::wasStatic() )
			static::__callStatic( __METHOD__, func_get_args() );
		else
			$this->instances = [];
	}

	/**
	 * Flush the bindings of all bindings and resolved instances.
	 */
	public function flush()
	{
		if ( static::wasStatic() )
			static::__callStatic( __METHOD__, func_get_args() );
		else
		{
			static::$framework->flush();

			$this->aliases = [];
			$this->resolved = [];
			$this->bindings = [];
			$this->instances = [];
		}
	}

	/**
	 * Determine if a given offset exists.
	 *
	 * @param  string $key
	 * @return bool
	 */
	public function offsetExists( $key )
	{
		return $this->bound( $key );
	}

	/**
	 * Get the value at a given offset.
	 *
	 * @param  string $key
	 * @return mixed
	 */
	public function offsetGet( $key )
	{
		return $this->make( $key );
	}

	/**
	 * Set the value at a given offset.
	 *
	 * @param  string $key
	 * @param  mixed $value
	 */
	public function offsetSet( $key, $value )
	{
		// If the value is not a Closure, we will make it one. This simply gives
		// more "drop-in" replacement functionality for the Pimple which this
		// bindings's simplest functions are base modeled and built after.
		if ( !$value instanceof Closure )
		{
			$value = function () use ( $value )
			{
				return $value;
			};
		}

		$this->bind( $key, $value );
	}

	/**
	 * Unset the value at a given offset.
	 *
	 * @param  string $key
	 */
	public function offsetUnset( $key )
	{
		$key = $this->normalize( $key );
		unset( $this->bindings[$key], $this->instances[$key], $this->resolved[$key] );
	}

	/**
	 * Dynamically access bindings services.
	 *
	 * @param  string $key
	 * @return mixed
	 */
	public function __get( $key )
	{
		return $this[$key];
	}

	/**
	 * Dynamically set bindings services.
	 *
	 * @param  string $key
	 * @param  mixed $value
	 * @return void
	 */
	public function __set( $key, $value )
	{
		$this[$key] = $value;
	}

	/**
	 * Dynamically access bindings services.
	 *
	 * @param  string $key
	 * @return mixed
	 */
	public static function get( $key )
	{
		return static::i()->make( $key );
	}
}
