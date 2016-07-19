<?php
namespace Penoaks\Routing;

use Closure;
use Penoaks\Bindings\Bindings;
use Penoaks\Contracts\Routing\Registrar as RegistrarContract;
use Penoaks\Database\Eloquent\Model;
use Penoaks\Events\Dispatcher;
use Penoaks\Http\Request;
use Penoaks\Http\Response;
use Penoaks\Support\Arr;
use Penoaks\Support\Collection;
use Penoaks\Support\Str;
use Penoaks\Support\Traits\Macroable;
use Penoaks\Traits\StaticAccess;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Router implements RegistrarContract
{
	use StaticAccess;

	/**
	 * The event dispatcher instance.
	 *
	 * @var Dispatcher
	 */
	protected $events;

	/**
	 * The IoC bindings instance.
	 *
	 * @var \Penoaks\Framework
	 */
	protected $bindings;

	/**
	 * The route collection instance.
	 *
	 * @var \Penoaks\Routing\RouteCollection
	 */
	protected $routes;

	/**
	 * The currently dispatched route instance.
	 *
	 * @var \Penoaks\Routing\Route
	 */
	protected $current;

	/**
	 * The request currently being dispatched.
	 *
	 * @var \Penoaks\Http\Request
	 */
	protected $currentRequest;

	/**
	 * All of the short-hand keys for middlewares.
	 *
	 * @var array
	 */
	protected $middleware = [];

	/**
	 * All of the middleware groups.
	 *
	 * @var array
	 */
	protected $middlewareGroups = [];

	/**
	 * The registered route value binders.
	 *
	 * @var array
	 */
	protected $binders = [];

	/**
	 * The globally available parameter patterns.
	 *
	 * @var array
	 */
	protected $patterns = [];

	/**
	 * The route group attribute stack.
	 *
	 * @var array
	 */
	protected $groupStack = [];

	/**
	 * All of the verbs supported by the router.
	 *
	 * @var array
	 */
	public static $verbs = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

	/**
	 * Create a new Router instance.
	 *
	 * @param  Dispatcher $events
	 * @param  Bindings $bindings
	 * @return void
	 */
	public function __construct( Dispatcher $events, Bindings $bindings = null )
	{
		$this->events = $events;
		$this->routes = new RouteCollection;
		$this->bindings = $bindings;

		static::$selfInstance = $this;

		$this->bind( '_missing', function ( $v )
		{
			return explode( '/', $v );
		} );
	}

	/**
	 * Register a new GET route with the router.
	 *
	 * @param  string $uri
	 * @param  \Closure|array|string|null $action
	 * @return \Penoaks\Routing\Route
	 */
	public function get( $uri, $action = null )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		return $this->addRoute( ['GET', 'HEAD'], $uri, $action );
	}

	/**
	 * Register a new POST route with the router.
	 *
	 * @param  string $uri
	 * @param  \Closure|array|string|null $action
	 * @return \Penoaks\Routing\Route
	 */
	public function post( $uri, $action = null )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		return $this->addRoute( 'POST', $uri, $action );
	}

	/**
	 * Register a new PUT route with the router.
	 *
	 * @param  string $uri
	 * @param  \Closure|array|string|null $action
	 * @return \Penoaks\Routing\Route
	 */
	public function put( $uri, $action = null )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		return $this->addRoute( 'PUT', $uri, $action );
	}

	/**
	 * Register a new PATCH route with the router.
	 *
	 * @param  string $uri
	 * @param  \Closure|array|string|null $action
	 * @return \Penoaks\Routing\Route
	 */
	public function patch( $uri, $action = null )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		return $this->addRoute( 'PATCH', $uri, $action );
	}

	/**
	 * Register a new DELETE route with the router.
	 *
	 * @param  string $uri
	 * @param  \Closure|array|string|null $action
	 * @return \Penoaks\Routing\Route
	 */
	public function delete( $uri, $action = null )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		return $this->addRoute( 'DELETE', $uri, $action );
	}

	/**
	 * Register a new OPTIONS route with the router.
	 *
	 * @param  string $uri
	 * @param  \Closure|array|string|null $action
	 * @return \Penoaks\Routing\Route
	 */
	public function options( $uri, $action = null )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		return $this->addRoute( 'OPTIONS', $uri, $action );
	}

	/**
	 * Register a new route responding to all verbs.
	 *
	 * @param  string $uri
	 * @param  \Closure|array|string|null $action
	 * @return \Penoaks\Routing\Route
	 */
	public function any( $uri, $action = null )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );
		$verbs = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE'];

		return $this->addRoute( $verbs, $uri, $action );
	}

	/**
	 * Register a new route with the given verbs.
	 *
	 * @param  array|string $methods
	 * @param  string $uri
	 * @param  \Closure|array|string|null $action
	 * @return \Penoaks\Routing\Route
	 */
	public function match( $methods, $uri, $action = null )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		return $this->addRoute( array_map( 'strtoupper', (array) $methods ), $uri, $action );
	}

	/**
	 * Set the unmapped global resource parameters to singular.
	 */
	public function singularResourceParameters()
	{
		if ( static::wasStatic() )
			static::__callStatic( __METHOD__, func_get_args() );
		else
			ResourceRegistrar::singularParameters();
	}

	/**
	 * Set the global resource parameter mapping.
	 *
	 * @param  array $parameters
	 */
	public function resourceParameters( array $parameters = [] )
	{
		if ( static::wasStatic() )
			static::__callStatic( __METHOD__, func_get_args() );
		else
			ResourceRegistrar::setParameters( $parameters );
	}

	/**
	 * Register an array of resource controllers.
	 *
	 * @param  array $resources
	 */
	public function resources( array $resources )
	{
		if ( static::wasStatic() )
			static::__callStatic( __METHOD__, func_get_args() );
		else
			foreach ( $resources as $name => $controller )
				$this->resource( $name, $controller );
	}

	/**
	 * Route a resource to a controller.
	 *
	 * @param  string $name
	 * @param  string $controller
	 * @param  array $options
	 */
	public function resource( $name, $controller, array $options = [] )
	{
		if ( static::wasStatic() )
			static::__callStatic( __METHOD__, func_get_args() );
		else
		{
			if ( $this->bindings && $this->bindings->bound( 'Penoaks\Routing\ResourceRegistrar' ) )
				$registrar = $this->bindings->make( 'Penoaks\Routing\ResourceRegistrar' );
			else
				$registrar = new ResourceRegistrar( $this );

			$registrar->register( $name, $controller, $options );
		}
	}

	/**
	 * Register the typical authentication routes for an application.
	 */
	public function auth()
	{
		if ( static::wasStatic() )
			static::__callStatic( __METHOD__, func_get_args() );
		else
		{
			// Authentication Routes...
			$this->get( 'login', 'Auth\AuthController@showLoginForm' );
			$this->post( 'login', 'Auth\AuthController@login' );
			$this->get( 'logout', 'Auth\AuthController@logout' );

			// Registration Routes...
			$this->get( 'register', 'Auth\AuthController@showRegistrationForm' );
			$this->post( 'register', 'Auth\AuthController@register' );

			// Password Reset Routes...
			$this->get( 'password/reset/{token?}', 'Auth\PasswordController@showResetForm' );
			$this->post( 'password/email', 'Auth\PasswordController@sendResetLinkEmail' );
			$this->post( 'password/reset', 'Auth\PasswordController@reset' );
		}
	}

	/**
	 * Create a route group with shared attributes.
	 *
	 * @param  array $attributes
	 * @param  \Closure $callback
	 */
	public function group( array $attributes, Closure $callback )
	{
		if ( static::wasStatic() )
			static::__callStatic( __METHOD__, func_get_args() );
		else
		{
			$this->updateGroupStack( $attributes );

			// Once we have updated the group stack, we will execute the user Closure and
			// merge in the groups attributes when the route is created. After we have
			// run the callback, we will pop the attributes off of this group stack.
			call_user_func( $callback, $this );

			array_pop( $this->groupStack );
		}
	}

	/**
	 * Update the group stack with the given attributes.
	 *
	 * @param  array $attributes
	 */
	protected function updateGroupStack( array $attributes )
	{
		if ( static::wasStatic() )
			static::__callStatic( __METHOD__, func_get_args() );
		else
		{
			if ( !empty( $this->groupStack ) )
			{
				$attributes = $this->mergeGroup( $attributes, end( $this->groupStack ) );
			}

			$this->groupStack[] = $attributes;
		}
	}

	/**
	 * Merge the given array with the last group stack.
	 *
	 * @param  array $new
	 * @return array
	 */
	public function mergeWithLastGroup( $new )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		return $this->mergeGroup( $new, end( $this->groupStack ) );
	}

	/**
	 * Merge the given group attributes.
	 *
	 * @param  array $new
	 * @param  array $old
	 * @return array
	 */
	public static function mergeGroup( $new, $old )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		$new['namespace'] = static::formatUsesPrefix( $new, $old );

		$new['prefix'] = static::formatGroupPrefix( $new, $old );

		if ( isset( $new['domain'] ) )
		{
			unset( $old['domain'] );
		}

		$new['where'] = array_merge( isset( $old['where'] ) ? $old['where'] : [], isset( $new['where'] ) ? $new['where'] : [] );

		if ( isset( $old['as'] ) )
		{
			$new['as'] = $old['as'] . ( isset( $new['as'] ) ? $new['as'] : '' );
		}

		return array_merge_recursive( Arr::except( $old, ['namespace', 'prefix', 'where', 'as'] ), $new );
	}

	/**
	 * Format the uses prefix for the new group attributes.
	 *
	 * @param  array $new
	 * @param  array $old
	 * @return string|null
	 */
	protected static function formatUsesPrefix( $new, $old )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		if ( isset( $new['namespace'] ) )
		{
			return isset( $old['namespace'] ) ? trim( $old['namespace'], '\\' ) . '\\' . trim( $new['namespace'], '\\' ) : trim( $new['namespace'], '\\' );
		}

		return isset( $old['namespace'] ) ? $old['namespace'] : null;
	}

	/**
	 * Format the prefix for the new group attributes.
	 *
	 * @param  array $new
	 * @param  array $old
	 * @return string|null
	 */
	protected static function formatGroupPrefix( $new, $old )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		$oldPrefix = isset( $old['prefix'] ) ? $old['prefix'] : null;

		if ( isset( $new['prefix'] ) )
		{
			return trim( $oldPrefix, '/' ) . '/' . trim( $new['prefix'], '/' );
		}

		return $oldPrefix;
	}

	/**
	 * Get the prefix from the last group on the stack.
	 *
	 * @return string
	 */
	public function getLastGroupPrefix()
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		if ( !empty( $this->groupStack ) )
		{
			$last = end( $this->groupStack );

			return isset( $last['prefix'] ) ? $last['prefix'] : '';
		}

		return '';
	}

	/**
	 * Add a route to the underlying route collection.
	 *
	 * @param  array|string $methods
	 * @param  string $uri
	 * @param  \Closure|array|string|null $action
	 * @return \Penoaks\Routing\Route
	 */
	protected function addRoute( $methods, $uri, $action )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		return $this->routes->add( $this->createRoute( $methods, $uri, $action ) );
	}

	/**
	 * Create a new route instance.
	 *
	 * @param  array|string $methods
	 * @param  string $uri
	 * @param  mixed $action
	 * @return \Penoaks\Routing\Route
	 */
	protected function createRoute( $methods, $uri, $action )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		// If the route is routing to a controller we will parse the route action into
		// an acceptable array format before registering it and creating this route
		// instance itself. We need to build the Closure that will call this out.
		if ( $this->actionReferencesController( $action ) )
			$action = $this->convertToControllerAction( $action );

		$route = $this->newRoute( $methods, $this->prefix( $uri ), $action );

		// If we have groups that need to be merged, we will merge them now after this
		// route has already been created and is ready to go. After we're done with
		// the merge we will be ready to return the route back out to the caller.
		if ( $this->hasGroupStack() )
		{
			$this->mergeGroupAttributesIntoRoute( $route );
		}

		$this->addWhereClausesToRoute( $route );

		return $route;
	}

	/**
	 * Create a new Route object.
	 *
	 * @param  array|string $methods
	 * @param  string $uri
	 * @param  mixed $action
	 * @return \Penoaks\Routing\Route
	 */
	protected function newRoute( $methods, $uri, $action )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		return ( new Route( $methods, $uri, $action ) )->setRouter( $this )->setBindings( $this->bindings );
	}

	/**
	 * Prefix the given URI with the last prefix.
	 *
	 * @param  string $uri
	 * @return string
	 */
	protected function prefix( $uri )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		return trim( trim( $this->getLastGroupPrefix(), '/' ) . '/' . trim( $uri, '/' ), '/' ) ?: '/';
	}

	/**
	 * Add the necessary where clauses to the route based on its initial registration.
	 *
	 * @param  \Penoaks\Routing\Route $route
	 * @return \Penoaks\Routing\Route
	 */
	protected function addWhereClausesToRoute( $route )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		$where = isset( $route->getAction()['where'] ) ? $route->getAction()['where'] : [];

		$route->where( array_merge( $this->patterns, $where ) );

		return $route;
	}

	/**
	 * Merge the group stack with the controller action.
	 *
	 * @param  \Penoaks\Routing\Route $route
	 */
	protected function mergeGroupAttributesIntoRoute( $route )
	{
		if ( static::wasStatic() )
			static::__callStatic( __METHOD__, func_get_args() );
		else
		{
			$action = $this->mergeWithLastGroup( $route->getAction() );
			$route->setAction( $action );
		}
	}

	/**
	 * Determine if the action is routing to a controller.
	 *
	 * @param  array $action
	 * @return bool
	 */
	protected function actionReferencesController( $action )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		if ( $action instanceof Closure )
			return false;

		return is_string( $action ) || ( isset( $action['uses'] ) && is_string( $action['uses'] ) );
	}

	/**
	 * Add a controller based route action to the action array.
	 *
	 * @param  array|string $action
	 * @return array
	 */
	protected function convertToControllerAction( $action )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		if ( is_string( $action ) )
			$action = ['uses' => $action];

		// Here we'll merge any group "uses" statement if necessary so that the action
		// has the proper clause for this property. Then we can simply set the name
		// of the controller on the action and return the action array for usage.
		if ( !empty( $this->groupStack ) )
			$action['uses'] = $this->prependGroupUses( $action['uses'] );

		// Here we will set this controller name on the action array just so we always
		// have a copy of it for reference if we need it. This can be used while we
		// search for a controller name or do some other type of fetch operation.
		$action['controller'] = $action['uses'];

		return $action;
	}

	/**
	 * Prepend the last group uses onto the use clause.
	 *
	 * @param  string $uses
	 * @return string
	 */
	protected function prependGroupUses( $uses )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		$group = end( $this->groupStack );

		return isset( $group['namespace'] ) && strpos( $uses, '\\' ) !== 0 ? $group['namespace'] . '\\' . $uses : $uses;
	}

	/**
	 * Dispatch the request to the application.
	 *
	 * @param  \Penoaks\Http\Request $request
	 * @return \Penoaks\Http\Response
	 */
	public function dispatch( Request $request )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		$this->currentRequest = $request;
		$response = $this->dispatchToRoute( $request );

		return $this->prepareResponse( $request, $response );
	}

	/**
	 * Dispatch the request to a route and return the response.
	 *
	 * @param  \Penoaks\Http\Request $request
	 * @return mixed
	 */
	public function dispatchToRoute( Request $request )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		// First we will find a route that matches this request. We will also set the
		// route resolver on the request so middlewares assigned to the route will
		// receive access to this route instance for checking of the parameters.
		$route = $this->findRoute( $request );

		$request->setRouteResolver( function () use ( $route )
		{
			return $route;
		} );

		$this->events->fire( new Events\RouteMatched( $route, $request ) );

		$response = $this->runRouteWithinStack( $route, $request );

		return $this->prepareResponse( $request, $response );
	}

	/**
	 * Run the given route within a Stack "onion" instance.
	 *
	 * @param  \Penoaks\Routing\Route $route
	 * @param  \Penoaks\Http\Request $request
	 * @return mixed
	 */
	protected function runRouteWithinStack( Route $route, Request $request )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		$shouldSkipMiddleware = $this->bindings->bound( 'middleware.disable' ) && $this->bindings->make( 'middleware.disable' ) === true;

		$middleware = $shouldSkipMiddleware ? [] : $this->gatherRouteMiddlewares( $route );

		return ( new Pipeline( $this->bindings ) )->send( $request )->through( $middleware )->then( function ( $request ) use ( $route )
		{
			return $this->prepareResponse( $request, $route->run( $request ) );
		} );
	}

	/**
	 * Gather the middleware for the given route.
	 *
	 * @param  \Penoaks\Routing\Route $route
	 * @return array
	 */
	public function gatherRouteMiddlewares( Route $route )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		return Collection::make( $route->middleware() )->map( function ( $name )
		{
			return Collection::make( $this->resolveMiddlewareClassName( $name ) );
		} )->flatten()->all();
	}

	/**
	 * Resolve the middleware name to a class name(s) preserving passed parameters.
	 *
	 * @param  string $name
	 * @return string|array
	 */
	public function resolveMiddlewareClassName( $name )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		$map = $this->middleware;

		// If the middleware is the name of a middleware group, we will return the array
		// of middlewares that belong to the group. This allows developers to group a
		// set of middleware under single keys that can be conveniently referenced.
		if ( isset( $this->middlewareGroups[$name] ) )
		{
			return $this->parseMiddlewareGroup( $name );
			// When the middleware is simply a Closure, we will return this Closure instance
			// directly so that Closures can be registered as middleware inline, which is
			// convenient on occasions when the developers are experimenting with them.
		}
		elseif ( isset( $map[$name] ) && $map[$name] instanceof Closure )
		{
			return $map[$name];
			// Finally, when the middleware is simply a string mapped to a class name the
			// middleware name will get parsed into the full class name and parameters
			// which may be run using the Pipeline which accepts this string format.
		}
		else
		{
			list( $name, $parameters ) = array_pad( explode( ':', $name, 2 ), 2, null );

			return ( isset( $map[$name] ) ? $map[$name] : $name ) . ( $parameters !== null ? ':' . $parameters : '' );
		}
	}

	/**
	 * Parse the middleware group and format it for usage.
	 *
	 * @param  string $name
	 * @return array
	 */
	protected function parseMiddlewareGroup( $name )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		$results = [];

		foreach ( $this->middlewareGroups[$name] as $middleware )
		{
			// If the middleware is another middleware group we will pull in the group and
			// merge its middleware into the results. This allows groups to conveniently
			// reference other groups without needing to repeat all their middlewares.
			if ( isset( $this->middlewareGroups[$middleware] ) )
			{
				$results = array_merge( $results, $this->parseMiddlewareGroup( $middleware ) );

				continue;
			}

			list( $middleware, $parameters ) = array_pad( explode( ':', $middleware, 2 ), 2, null );

			// If this middleware is actually a route middleware, we will extract the full
			// class name out of the middleware list now. Then we'll add the parameters
			// back onto this class' name so the pipeline will properly extract them.
			if ( isset( $this->middleware[$middleware] ) )
			{
				$middleware = $this->middleware[$middleware];
			}

			$results[] = $middleware . ( $parameters ? ':' . $parameters : '' );
		}

		return $results;
	}

	/**
	 * Find the route matching a given request.
	 *
	 * @param  \Penoaks\Http\Request $request
	 * @return \Penoaks\Routing\Route
	 */
	protected function findRoute( $request )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		$this->current = $route = $this->routes->match( $request );

		$this->bindings->instance( 'Penoaks\Routing\Route', $route );

		return $this->substituteBindings( $route );
	}

	/**
	 * Substitute the route bindings onto the route.
	 *
	 * @param  \Penoaks\Routing\Route $route
	 * @return \Penoaks\Routing\Route
	 */
	protected function substituteBindings( $route )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		foreach ( $route->parameters() as $key => $value )
			if ( isset( $this->binders[$key] ) )
				$route->setParameter( $key, $this->performBinding( $key, $value, $route ) );

		$this->substituteImplicitBindings( $route );

		return $route;
	}

	/**
	 * Substitute the implicit Eloquent model bindings for the route.
	 *
	 * @param  \Penoaks\Routing\Route $route
	 */
	protected function substituteImplicitBindings( $route )
	{
		if ( static::wasStatic() )
			static::__callStatic( __METHOD__, func_get_args() );
		else
		{
			$parameters = $route->parameters();

			foreach ( $route->signatureParameters( Model::class ) as $parameter )
			{
				$class = $parameter->getClass();

				if ( array_key_exists( $parameter->name, $parameters ) && !$route->getParameter( $parameter->name ) instanceof Model )
				{
					$method = $parameter->isDefaultValueAvailable() ? 'first' : 'firstOrFail';

					$model = $class->newInstance();

					$route->setParameter( $parameter->name, $model->where( $model->getRouteKeyName(), $parameters[$parameter->name] )->{$method}() );
				}
			}
		}
	}

	/**
	 * Call the binding callback for the given key.
	 *
	 * @param  string $key
	 * @param  string $value
	 * @param  \Penoaks\Routing\Route $route
	 * @return mixed
	 */
	protected function performBinding( $key, $value, $route )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		return call_user_func( $this->binders[$key], $value, $route );
	}

	/**
	 * Register a route matched event listener.
	 *
	 * @param  string|callable $callback
	 * @return void
	 */
	public function matched( $callback )
	{
		if ( static::wasStatic() )
			static::__callStatic( __METHOD__, func_get_args() );
		else
			$this->events->listen( Events\RouteMatched::class, $callback );
	}

	/**
	 * Get all of the defined middleware short-hand names.
	 *
	 * @return array
	 */
	public function getMiddleware()
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		return $this->middleware;
	}

	/**
	 * Register a short-hand name for a middleware.
	 *
	 * @param  string $name
	 * @param  string $class
	 * @return $this
	 */
	public function middleware( $name, $class )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );
		$this->middleware[$name] = $class;

		return $this;
	}

	/**
	 * Register a group of middleware.
	 *
	 * @param  string $name
	 * @param  array $middleware
	 * @return $this
	 */
	public function middlewareGroup( $name, array $middleware )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );
		$this->middlewareGroups[$name] = $middleware;

		return $this;
	}

	/**
	 * Add a middleware to the beginning of a middleware group.
	 *
	 * If the middleware is already in the group, it will not be added again.
	 *
	 * @param  string $group
	 * @param  string $middleware
	 * @return $this
	 */
	public function prependMiddlewareToGroup( $group, $middleware )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		if ( isset( $this->middlewareGroups[$group] ) && !in_array( $middleware, $this->middlewareGroups[$group] ) )
			array_unshift( $this->middlewareGroups[$group], $middleware );

		return $this;
	}

	/**
	 * Add a middleware to the end of a middleware group.
	 *
	 * If the middleware is already in the group, it will not be added again.
	 *
	 * @param  string $group
	 * @param  string $middleware
	 * @return $this
	 */
	public function pushMiddlewareToGroup( $group, $middleware )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		if ( isset( $this->middlewareGroups[$group] ) && !in_array( $middleware, $this->middlewareGroups[$group] ) )
			$this->middlewareGroups[$group][] = $middleware;

		return $this;
	}

	/**
	 * Register a model binder for a wildcard.
	 *
	 * @param  string $key
	 * @param  string $class
	 * @param  \Closure|null $callback
	 *
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 */
	public function model( $key, $class, Closure $callback = null )
	{
		if ( static::wasStatic() )
			static::__callStatic( __METHOD__, func_get_args() );
		else
		{
			$this->bind( $key, function ( $value ) use ( $class, $callback )
			{
				if ( is_null( $value ) )
					return null;

				// For model binders, we will attempt to retrieve the models using the first
				// method on the model instance. If we cannot retrieve the models we'll
				// throw a not found exception otherwise we will return the instance.
				$instance = $this->bindings->make( $class );

				if ( $model = $instance->where( $instance->getRouteKeyName(), $value )->first() )
				{
					return $model;
				}

				// If a callback was supplied to the method we will call that to determine
				// what we should do when the model is not found. This just gives these
				// developer a little greater flexibility to decide what will happen.
				if ( $callback instanceof Closure )
				{
					return call_user_func( $callback, $value );
				}

				throw new NotFoundHttpException;
			} );
		}
	}

	/**
	 * Add a new route parameter binder.
	 *
	 * @param  string $key
	 * @param  string|callable $binder
	 */
	public function bind( $key, $binder )
	{
		if ( static::wasStatic() )
			static::__callStatic( __METHOD__, func_get_args() );
		else
		{
			if ( is_string( $binder ) )
				$binder = $this->createClassBinding( $binder );

			$this->binders[str_replace( '-', '_', $key )] = $binder;
		}
	}

	/**
	 * Create a class based binding using the IoC bindings.
	 *
	 * @param  string $binding
	 * @return \Closure
	 */
	public function createClassBinding( $binding )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		return function ( $value, $route ) use ( $binding )
		{
			// If the binding has an @ sign, we will assume it's being used to delimit
			// the class name from the bind method name. This allows for bindings
			// to run multiple bind methods in a single class for convenience.
			$segments = explode( '@', $binding );

			$method = count( $segments ) == 2 ? $segments[1] : 'bind';

			$callable = [$this->bindings->make( $segments[0] ), $method];

			return call_user_func( $callable, $value, $route );
		};
	}

	/**
	 * Set a global where pattern on all routes.
	 *
	 * @param  string $key
	 * @param  string $pattern
	 */
	public function pattern( $key, $pattern )
	{
		if ( static::wasStatic() )
			static::__callStatic( __METHOD__, func_get_args() );
		else
			$this->patterns[$key] = $pattern;
	}

	/**
	 * Set a group of global where patterns on all routes.
	 *
	 * @param  array $patterns
	 */
	public function patterns( $patterns )
	{
		if ( static::wasStatic() )
			static::__callStatic( __METHOD__, func_get_args() );
		else
			foreach ( $patterns as $key => $pattern )
			{
				$this->pattern( $key, $pattern );
			}
	}

	/**
	 * Create a response instance from the given value.
	 *
	 * @param  \Symfony\Component\HttpFoundation\Request $request
	 * @param  mixed $response
	 * @return \Penoaks\Http\Response
	 */
	public function prepareResponse( $request, $response )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		if ( $response instanceof PsrResponseInterface )
			$response = ( new HttpFoundationFactory )->createResponse( $response );
		elseif ( !$response instanceof SymfonyResponse )
			$response = new Response( $response );

		return $response->prepare( $request );
	}

	/**
	 * Determine if the router currently has a group stack.
	 *
	 * @return bool
	 */
	public function hasGroupStack()
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		return !empty( $this->groupStack );
	}

	/**
	 * Get the current group stack for the router.
	 *
	 * @return array
	 */
	public function getGroupStack()
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		return $this->groupStack;
	}

	/**
	 * Get a route parameter for the current route.
	 *
	 * @param  string $key
	 * @param  string $default
	 * @return mixed
	 */
	public function input( $key, $default = null )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		return $this->current()->parameter( $key, $default );
	}

	/**
	 * Get the currently dispatched route instance.
	 *
	 * @return \Penoaks\Routing\Route
	 */
	public function getCurrentRoute()
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		return $this->current();
	}

	/**
	 * Get the currently dispatched route instance.
	 *
	 * @return \Penoaks\Routing\Route
	 */
	public function current()
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		return $this->current;
	}

	/**
	 * Check if a route with the given name exists.
	 *
	 * @param  string $name
	 * @return bool
	 */
	public function has( $name )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		return $this->routes->hasNamedRoute( $name );
	}

	/**
	 * Get the current route name.
	 *
	 * @return string|null
	 */
	public function currentRouteName()
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		return $this->current() ? $this->current()->getName() : null;
	}

	/**
	 * Alias for the "currentRouteName" method.
	 *
	 * @param  mixed  string
	 * @return bool
	 */
	public function is()
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		foreach ( func_get_args() as $pattern )
		{
			if ( Str::is( $pattern, $this->currentRouteName() ) )
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if the current route matches a given name.
	 *
	 * @param  string $name
	 * @return bool
	 */
	public function currentRouteNamed( $name )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		return $this->current() ? $this->current()->getName() == $name : false;
	}

	/**
	 * Get the current route action.
	 *
	 * @return string|null
	 */
	public function currentRouteAction()
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		if ( !$this->current() )
			return;

		$action = $this->current()->getAction();

		return isset( $action['controller'] ) ? $action['controller'] : null;
	}

	/**
	 * Alias for the "currentRouteUses" method.
	 *
	 * @param  mixed  string
	 * @return bool
	 */
	public function uses()
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		foreach ( func_get_args() as $pattern )
			if ( Str::is( $pattern, $this->currentRouteAction() ) )
				return true;

		return false;
	}

	/**
	 * Determine if the current route action matches a given action.
	 *
	 * @param  string $action
	 * @return bool
	 */
	public function currentRouteUses( $action )
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		return $this->currentRouteAction() == $action;
	}

	/**
	 * Get the request currently being dispatched.
	 *
	 * @return \Penoaks\Http\Request
	 */
	public function getCurrentRequest()
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		return $this->currentRequest;
	}

	/**
	 * Get the underlying route collection.
	 *
	 * @return \Penoaks\Routing\RouteCollection
	 */
	public function getRoutes()
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		return $this->routes;
	}

	/**
	 * Set the route collection instance.
	 *
	 * @param  \Penoaks\Routing\RouteCollection $routes
	 */
	public function setRoutes( RouteCollection $routes )
	{
		if ( static::wasStatic() )
			static::__callStatic( __METHOD__, func_get_args() );
		else
		{
			foreach ( $routes as $route )
			{
				$route->setRouter( $this )->setBindings( $this->bindings );
			}

			$this->routes = $routes;
			$this->bindings->instance( 'routes', $this->routes );
		}
	}

	/**
	 * Get the global "where" patterns.
	 *
	 * @return array
	 */
	public function getPatterns()
	{
		if ( static::wasStatic() )
			return static::__callStatic( __METHOD__, func_get_args() );

		return $this->patterns;
	}
}
