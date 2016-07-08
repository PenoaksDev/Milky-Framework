<?php

namespace Penoaks\Routing;

use BadMethodCallException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class Controller
{
	/**
	 * The middleware registered on the controller.
	 *
	 * @var array
	 */
	protected $middleware = [];

	/**
	 * The router instance.
	 *
	 * @var \Penoaks\Routing\Router
	 */
	protected static $router;

	/**
	 * Register middleware on the controller.
	 *
	 * @param  array|string  $middleware
	 * @param  array   $options
	 * @return \Penoaks\Routing\ControllerMiddlewareOptions
	 */
	public function middleware($middleware, array $options = [])
	{
		foreach ((array) $middleware as $middlewareName)
{
			$this->middleware[$middlewareName] = &$options;
		}

		return new ControllerMiddlewareOptions($options);
	}

	/**
	 * Get the middleware assigned to the controller.
	 *
	 * @return array
	 */
	public function getMiddleware()
	{
		return $this->middleware;
	}

	/**
	 * Get the router instance.
	 *
	 * @return \Penoaks\Routing\Router
	 */
	public static function getRouter()
	{
		return static::$router;
	}

	/**
	 * Set the router instance.
	 *
	 * @param  \Penoaks\Routing\Router  $router
	 * @return void
	 */
	public static function setRouter(Router $router)
	{
		static::$router = $router;
	}

	/**
	 * Execute an action on the controller.
	 *
	 * @param  string  $method
	 * @param  array   $parameters
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function callAction($method, $parameters)
	{
		return call_user_func_array([$this, $method], $parameters);
	}

	/**
	 * Handle calls to missing methods on the controller.
	 *
	 * @param  array   $parameters
	 * @return mixed
	 *
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 */
	public function missingMethod($parameters = [])
	{
		throw new NotFoundHttpException('Controller method not found.');
	}

	/**
	 * Handle calls to missing methods on the controller.
	 *
	 * @param  string  $method
	 * @param  array   $parameters
	 * @return mixed
	 *
	 * @throws \BadMethodCallException
	 */
	public function __call($method, $parameters)
	{
		throw new BadMethodCallException("Method [$method] does not exist.");
	}
}
