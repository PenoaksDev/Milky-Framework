<?php namespace Milky\Http;

use Milky\Binding\UniversalBuilder;
use Milky\Exceptions\Handler;
use Milky\Facades\Hooks;
use Milky\Framework;
use Milky\Http\Middleware\EncryptCookies;
use Milky\Http\Middleware\ShareSessionMessages;
use Milky\Http\Routing\Redirector;
use Milky\Http\Routing\Router;
use Milky\Http\Routing\UrlGenerator;
use Milky\Http\Session\SessionManager;
use Milky\Impl\Extendable;
use Milky\Pipeline\Pipeline;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class HttpFactory
{
	use Extendable;

	/**
	 * @var Framework
	 */
	private $fw;

	/**
	 * @var Router
	 */
	private $router;

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var UrlGenerator
	 */
	private $url;

	/**
	 * @var Response
	 */
	private $response = null;

	/**
	 * @var Redirector
	 */
	private $redirector;

	/**
	 * @var array
	 */
	private $middleware = [];

	/**
	 * Should middleware be disabled?
	 *
	 * @var bool
	 */
	private $disableMiddleware = false;

	/**
	 * @param bool $bool
	 */
	public function disableMiddleware( $bool = true )
	{
		$this->disableMiddleware = $bool;
	}

	/**
	 * @return bool
	 */
	public function isMiddlewareDisabled()
	{
		return $this->disableMiddleware;
	}

	/**
	 * @return HttpFactory
	 */
	public static function i()
	{
		return UniversalBuilder::resolve( 'http.factory' );
	}

	public function __construct( Framework $fw, Request $request = null )
	{
		Hooks::trigger( 'http.factory.create', ['factory' => $this] );

		$this->request = $request ?: $request = Request::capture();
		$this->request->setSession( SessionManager::i()->driver() );

		$this->fw = $fw;

		$r = new Router();

		$routes = $r->getRoutes();

		$url = new UrlGenerator( $routes, $request );

		$url->setSessionResolver( function ()
		{
			return Framework::get( 'session' );
		} );

		$redirector = new Redirector( $url );
		$redirector->setSession( SessionManager::i()->driver() );

		$this->redirector = $redirector;
		$this->router = $r;
		$this->url = $url;
	}

	/**
	 * @return Redirector
	 */
	public function redirector()
	{
		return $this->redirector;
	}

	/**
	 * Set the root controller namespace.
	 *
	 * @param  string $namespace
	 * @return $this
	 */
	public function setRootControllerNamespace( $namespace )
	{
		$this->url->setRootControllerNamespace( $namespace );

		return $this;
	}

	public function url()
	{
		return $this->url;
	}

	public function router()
	{
		return $this->router;
	}

	public function routeRequest()
	{
		$this->addMiddleware( [
			new EncryptCookies( Framework::get( 'encrypter' ) ),
			SessionManager::i(),
			ShareSessionMessages::class,
		] );

		$this->router->getRoutes()->refreshNameLookups();

		$this->response = ( new Pipeline() )->withExceptionHandler( function ( $request, $e )
		{
			Handler::i()->handleException( $e, $request );
			die();
		} )->send( $this->request )->through( $this->middleware )->then( function ( $request )
		{
			return $this->router->dispatch( $request );
		} );

		return $this->response;
	}

	public function terminate( $request = null, $response = null )
	{
		$request = $request ?: $this->request;
		$response = $response ?: $this->response;

		$middlewares = $this->isMiddlewareDisabled() ? [] : array_merge( $this->gatherRouteMiddlewares( $request ), $this->middleware );

		foreach ( $middlewares as $middleware )
		{
			if ( is_object( $middleware ) )
				$instance = $middleware;
			else
			{
				list( $name, $parameters ) = $this->parseMiddleware( $middleware );
				$instance = UniversalBuilder::resolve( $name );
			}

			if ( method_exists( $instance, 'terminate' ) )
				$instance->terminate( $request, $response );
		}

		Hooks::trigger( 'app.terminate' );

		Framework::fw()->terminate();
	}

	/**
	 * @return Request
	 */
	public function request()
	{
		return $this->request;
	}

	/**
	 * @return Response
	 */
	public function response()
	{
		return $this->response;
	}

	/**
	 * @param array $middleware
	 */
	public function addMiddleware( array $middleware )
	{
		$this->middleware = array_merge( $this->middleware, $middleware );
	}

	/**
	 * @param string $key
	 * @param array $middleware
	 */
	public function addMiddlewareGroup( $key, array $middleware )
	{
		$this->router->middlewareGroup( $key, $middleware );
	}

	/**
	 * @param string $key
	 * @param array $middleware
	 */
	public function addRouteMiddleware( $key, array $middleware )
	{
		$this->router->middleware( $key, $middleware );
	}

	/**
	 * Gather the route middleware for the given request.
	 *
	 * @param  Request $request
	 *
	 * @return array
	 */
	protected function gatherRouteMiddlewares( Request $request )
	{
		if ( $route = $request->route() )
			return $this->router()->gatherRouteMiddlewares( $route );
	}

	/**
	 * Parse a middleware string to get the name and parameters.
	 *
	 * @param  string $middleware
	 *
	 * @return array
	 */
	protected function parseMiddleware( $middleware )
	{
		list( $name, $parameters ) = array_pad( explode( ':', $middleware, 2 ), 2, [] );

		if ( is_string( $parameters ) )
			$parameters = explode( ',', $parameters );

		return [$name, $parameters];
	}
}
