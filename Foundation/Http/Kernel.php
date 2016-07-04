<?php

namespace Foundation\Http;

use Exception;
use Throwable;
use Foundation\Routing\Router;
use Foundation\Routing\Pipeline;
use Foundation\Framework;
use Foundation\Events\Runlevel;
use Foundation\Support\Facades\Facade;
use Foundation\Contracts\Http\Kernel as KernelContract;
use Symfony\Component\Debug\Exception\FatalThrowableError;

class Kernel implements KernelContract
{
	/**
	 * The application implementation.
	 *
	 * @var \Foundation\Framework
	 */
	protected $fw;

	/**
	 * The router instance.
	 *
	 * @var \Foundation\Routing\Router
	 */
	protected $router;

	/**
	 * The application's middleware stack.
	 *
	 * @var array
	 */
	protected $middleware = [];

	/**
	 * The application's route middleware groups.
	 *
	 * @var array
	 */
	protected $middlewareGroups = [];

	/**
	 * The application's route middleware.
	 *
	 * @var array
	 */
	protected $routeMiddleware = [];

	/**
	 * Create a new HTTP kernel instance.
	 *
	 * @param  \Foundation\Framework $fw
	 * @param  \Foundation\Routing\Router $router
	 * @return void
	 */
	public function __construct( Framework $fw, Router $router )
	{
		$this->fw = $fw;
		$this->router = $router;

		foreach ( $this->middlewareGroups as $key => $middleware )
		{
			$router->middlewareGroup( $key, $middleware );
		}

		foreach ( $this->routeMiddleware as $key => $middleware )
		{
			$router->middleware( $key, $middleware );
		}

		$this->fw->bindings['events']->listenEvents( $this );
	}

	/**
	 * Listens for when the Framework Runlevel Changes
	 */
	public function onRunlevel( Runlevel $event )
	{
		switch ( Runlevel::$level )
		{
			case Runlevel::INITIALIZING:
				$this->fw->bootstrap( ['Foundation\Bootstrap\DetectEnvironment',
					'Foundation\Bootstrap\LoadConfiguration',
					'Foundation\Bootstrap\ConfigureLogging',
					'Foundation\Bootstrap\HandleExceptions',
					'Foundation\Bootstrap\RegisterFacades',
					'Foundation\Bootstrap\RegisterProviders',
					'Foundation\Bootstrap\BootProviders',] );
				break;
		}
	}

	/**
	 * Handle an incoming HTTP request.
	 *
	 * @param  \Foundation\Http\Request $request
	 * @return \Foundation\Http\Response
	 */
	public function handle( $request )
	{
		try
		{
			$request->enableHttpMethodParameterOverride();

			$response = $this->sendRequestThroughRouter( $request );
		}
		catch ( Exception $e )
		{
			$this->fw->reportException( $e );

			$response = $this->fw->renderException( $request, $e );
		}
		catch ( Throwable $e )
		{
			$this->fw->reportException( $e = new FatalThrowableError( $e ) );

			$response = $this->fw->renderException( $request, $e );
		}

		$this->fw->bindings['events']->fire( 'kernel.handled', [$request, $response] );

		return $response;
	}

	/**
	 * Send the given request through the middleware / router.
	 *
	 * @param  \Foundation\Http\Request $request
	 * @return \Foundation\Http\Response
	 */
	protected function sendRequestThroughRouter( $request )
	{
		$this->fw->bindings->instance( 'request', $request );
		Facade::clearResolvedInstance( 'request' );

		return ( new Pipeline( $this->fw->bindings ) )->send( $request )->through( $this->fw->shouldSkipMiddleware() ? [] : $this->middleware )->then( $this->dispatchToRouter() );
	}

	/**
	 * Call the terminate method on any terminable middleware.
	 *
	 * @param  \Foundation\Http\Request $request
	 * @param  \Foundation\Http\Response $response
	 * @return void
	 */
	public function terminate( $request, $response )
	{
		$middlewares = $this->fw->shouldSkipMiddleware() ? [] : array_merge( $this->gatherRouteMiddlewares( $request ), $this->middleware );

		foreach ( $middlewares as $middleware )
		{
			list( $name, $parameters ) = $this->parseMiddleware( $middleware );

			$instance = $this->fw->make( $name );

			if ( method_exists( $instance, 'terminate' ) )
			{
				$instance->terminate( $request, $response );
			}
		}

		$this->fw->terminate();
	}

	/**
	 * Gather the route middleware for the given request.
	 *
	 * @param  \Foundation\Http\Request $request
	 * @return array
	 */
	protected function gatherRouteMiddlewares( $request )
	{
		if ( $route = $request->route() )
		{
			return $this->router->gatherRouteMiddlewares( $route );
		}

		return [];
	}

	/**
	 * Parse a middleware string to get the name and parameters.
	 *
	 * @param  string $middleware
	 * @return array
	 */
	protected function parseMiddleware( $middleware )
	{
		list( $name, $parameters ) = array_pad( explode( ':', $middleware, 2 ), 2, [] );

		if ( is_string( $parameters ) )
		{
			$parameters = explode( ',', $parameters );
		}

		return [$name, $parameters];
	}

	/**
	 * Add a new middleware to beginning of the stack if it does not already exist.
	 *
	 * @param  string $middleware
	 * @return $this
	 */
	public function prependMiddleware( $middleware )
	{
		if ( array_search( $middleware, $this->middleware ) === false )
		{
			array_unshift( $this->middleware, $middleware );
		}

		return $this;
	}

	/**
	 * Add a new middleware to end of the stack if it does not already exist.
	 *
	 * @param  string $middleware
	 * @return $this
	 */
	public function pushMiddleware( $middleware )
	{
		if ( array_search( $middleware, $this->middleware ) === false )
		{
			$this->middleware[] = $middleware;
		}

		return $this;
	}

	/**
	 * Get the route dispatcher callback.
	 *
	 * @return \Closure
	 */
	protected function dispatchToRouter()
	{
		return function ( $request )
		{
			$this->fw->bindings->instance( 'request', $request );

			return $this->router->dispatch( $request );
		};
	}

	/**
	 * Determine if the kernel has a given middleware.
	 *
	 * @param  string $middleware
	 * @return bool
	 */
	public function hasMiddleware( $middleware )
	{
		return in_array( $middleware, $this->middleware );
	}

	/**
	 * Get the Framework application instance.
	 *
	 * @return \Foundation\Framework
	 */
	public function getApplication()
	{
		return $this->fw;
	}
}
