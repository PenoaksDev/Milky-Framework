<?php
namesapce Penoaks\Barebones;

use Exception;
use Foundation\Events\Runlevel;
use Foundation\Framework;
use Foundation\Framework\Env;
use Foundation\Routing\Pipeline;
use Foundation\Routing\Router;
use Foundation\Support\Facades\Facade;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Throwable;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
abstract class Kernel
{
	/**
	 * The application implementation.
	 *
	 * @var \Penoaks\Framework
	 */
	protected $fw;

	/**
	 * The router instance.
	 *
	 * @var \Penoaks\Routing\Router
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
	 * @param  \Penoaks\Framework $fw
	 * @param  \Penoaks\Routing\Router $router
	 * @return void
	 */
	public function __construct( Framework $fw, Env $env )
	{
		$this->fw = $fw;
		$router = Router::i();
		$this->router = &$router;

		foreach ( $this->middlewareGroups as $key => $middleware )
		{
			$router->middlewareGroup( $key, $middleware );
		}

		foreach ( $this->routeMiddleware as $key => $middleware )
		{
			$router->middleware( $key, $middleware );
		}

		// Registers implemented event listeners with the events class, e.g., public function onSomethingEvent( ThrowableEvent $event );
		$this->fw->bindings['events']->listenEvents( $this );
	}

	/**
	 * Listens for when the Framework Runlevel Changes
	 */
	public function onRunlevelEvent( Runlevel $event )
	{
		switch ( $event->get() )
		{
			case Runlevel::BOOT:
				$this->fw->bootstrap( [
				] );
				break;
		}
	}

	/**
	 * Handle an incoming HTTP request.
	 *
	 * @param  \Penoaks\Http\Request $request
	 * @return \Penoaks\Http\Response
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
	 * @param  \Penoaks\Http\Request $request
	 * @return \Penoaks\Http\Response
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
	 * @param  \Penoaks\Http\Request $request
	 * @param  \Penoaks\Http\Response $response
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
	 * @param  \Penoaks\Http\Request $request
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
	 * @return \Penoaks\Framework
	 */
	public function getApplication()
	{
		return $this->fw;
	}
}
