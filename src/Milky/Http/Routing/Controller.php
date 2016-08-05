<?php namespace Milky\Http\Routing;

use BadMethodCallException;
use Milky\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
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
	 * @var Router
	 */
	protected static $router;

	/**
	 * Register middleware on the controller.
	 *
	 * @param  array|string $middleware
	 * @param  array $options
	 * @return ControllerMiddlewareOptions
	 */
	public function middleware( $middleware, array $options = [] )
	{
		foreach ( (array) $middleware as $middlewareName )
			$this->middleware[$middlewareName] = &$options;

		return new ControllerMiddlewareOptions( $options );
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
	 * @return Router
	 */
	public static function getRouter()
	{
		return static::$router;
	}

	/**
	 * Set the router instance.
	 *
	 * @param  Router $router
	 */
	public static function setRouter( Router $router )
	{
		static::$router = $router;
	}

	/**
	 * Execute an action on the controller.
	 *
	 * @param  string $method
	 * @param  array $parameters
	 * @return Response
	 */
	public function callAction( $method, $parameters )
	{
		return call_user_func_array( [$this, $method], $parameters );
	}

	/**
	 * Handle calls to missing methods on the controller.
	 *
	 * @param  array $parameters
	 * @return mixed
	 *
	 * @throws NotFoundHttpException
	 */
	public function missingMethod( $parameters = [] )
	{
		throw new NotFoundHttpException( 'Controller method not found.' );
	}

	/**
	 * Handle calls to missing methods on the controller.
	 *
	 * @param  string $method
	 * @param  array $parameters
	 * @return mixed
	 *
	 * @throws \BadMethodCallException
	 */
	public function __call( $method, $parameters )
	{
		throw new BadMethodCallException( "Method [$method] does not exist." );
	}

	/**
	 * Produces a new error response based on if this is an api request or not.
	 *
	 * @param int $code
	 * @param string $msg
	 * @return JsonResponse|Response
	 */
	public function error( $code = 404, $msg = "Resource not found" )
	{
		$content = ['error' => $msg];

		return ( request()->ajax() || request()->wantsJson() ) ? new JsonResponse( $content, $code ) : new Response( $content, $code );
	}
}
