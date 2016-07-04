<?php

namespace Foundation\Routing;

use Foundation\Http\Request;
use Foundation\Support\Collection;
use Foundation\Framework;

class ControllerDispatcher
{
	use RouteDependencyResolverTrait;

	/**
	 * The router instance.
	 *
	 * @var \Foundation\Routing\Router
	 */
	protected $router;

	/**
	 * The IoC bindings instance.
	 *
	 * @var \Foundation\Framework
	 */
	protected $bindings;

	/**
	 * Create a new controller dispatcher instance.
	 *
	 * @param  \Foundation\Routing\Router  $router
	 * @param  \Foundation\Framework  $bindings
	 * @return void
	 */
	public function __construct(Router $router,
								Bindings $bindings = null)
	{
		$this->router = $router;
		$this->bindings = $bindings;
	}

	/**
	 * Dispatch a request to a given controller and method.
	 *
	 * @param  \Foundation\Routing\Route  $route
	 * @param  \Foundation\Http\Request  $request
	 * @param  string  $controller
	 * @param  string  $method
	 * @return mixed
	 */
	public function dispatch(Route $route, Request $request, $controller, $method)
	{
		$instance = $this->makeController($controller);

		return $this->callWithinStack($instance, $route, $request, $method);
	}

	/**
	 * Make a controller instance via the IoC bindings.
	 *
	 * @param  string  $controller
	 * @return mixed
	 */
	protected function makeController($controller)
	{
		Controller::setRouter($this->router);

		return $this->bindings->make($controller);
	}

	/**
	 * Call the given controller instance method.
	 *
	 * @param  \Foundation\Routing\Controller  $instance
	 * @param  \Foundation\Routing\Route  $route
	 * @param  \Foundation\Http\Request  $request
	 * @param  string  $method
	 * @return mixed
	 */
	protected function callWithinStack($instance, $route, $request, $method)
	{
		$shouldSkipMiddleware = $this->bindings->bound('middleware.disable') &&
								$this->bindings->make('middleware.disable') === true;

		$middleware = $shouldSkipMiddleware ? [] : $this->getMiddleware($instance, $method);

		// Here we will make a stack onion instance to execute this request in, which gives
		// us the ability to define middlewares on controllers. We will return the given
		// response back out so that "after" filters can be run after the middlewares.
		return (new Pipeline($this->bindings))
					->send($request)
					->through($middleware)
					->then(function ($request) use ($instance, $route, $method)
{
						return $this->router->prepareResponse(
							$request, $this->call($instance, $route, $method)
						);
					});
	}

	/**
	 * Get the middleware for the controller instance.
	 *
	 * @param  \Foundation\Routing\Controller  $instance
	 * @param  string  $method
	 * @return array
	 */
	public function getMiddleware($instance, $method)
	{
		$results = new Collection;

		foreach ($instance->getMiddleware() as $name => $options)
{
			if (! $this->methodExcludedByOptions($method, $options))
{
				$results[] = $this->router->resolveMiddlewareClassName($name);
			}
		}

		return $results->flatten()->all();
	}

	/**
	 * Determine if the given options exclude a particular method.
	 *
	 * @param  string  $method
	 * @param  array  $options
	 * @return bool
	 */
	public function methodExcludedByOptions($method, array $options)
	{
		return (isset($options['only']) && ! in_array($method, (array) $options['only'])) ||
			(! empty($options['except']) && in_array($method, (array) $options['except']));
	}

	/**
	 * Call the given controller instance method.
	 *
	 * @param  \Foundation\Routing\Controller  $instance
	 * @param  \Foundation\Routing\Route  $route
	 * @param  string  $method
	 * @return mixed
	 */
	protected function call($instance, $route, $method)
	{
		$parameters = $this->resolveClassMethodDependencies(
			$route->parametersWithoutNulls(), $instance, $method
		);

		return $instance->callAction($method, $parameters);
	}
}
