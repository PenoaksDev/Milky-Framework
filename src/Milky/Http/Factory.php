<?php namespace Milky\Http;

use Milky\Framework;
use Milky\Http\Cookies\CookieJar;
use Milky\Http\Middleware\EncryptCookies;
use Milky\Http\Routing\Redirector;
use Milky\Http\Routing\ResponseFactory;
use Milky\Http\Routing\Router;
use Milky\Http\Routing\UrlGenerator;
use Milky\Http\Session\Middleware\StartSession;
use Milky\Pipeline\Pipeline;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Zend\Diactoros\Response as PsrResponse;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class Factory
{
	/**
	 * @var Framework
	 */
	private $fw;

	/**
	 * @var Router
	 */
	private $router;

	/**
	 * @var CookieJar
	 */
	private $cookies;

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
	 * @var array
	 */
	private $middleware = [];

	public function __construct( Framework $fw, Request $request = null )
	{
		if ( !$request )
			$request = Request::capture();

		$this->request = $request;
		Framework::set( 'http.request', $request );

		$this->fw = $fw;

		$config = $fw->config['config']['session'];
		$this->cookies = ( new CookieJar() )->setDefaultPathAndDomain( $config['path'], $config['domain'], $config['secure'] );

		$r = new Router();

		$routes = $r->getRoutes();

		$url = new UrlGenerator( $routes, $request );

		$url->setSessionResolver( function ()
		{
			return Framework::get( 'session' );
		} );

		$redirector = new Redirector( $url );

		if ( Framework::available( 'session.store' ) )
			$redirector->setSession( Framework::get( 'session.store' ) );

		Framework::set( 'router', function ()
		{
			return $this->router();
		} );

		Framework::set( 'url', function ()
		{
			return $this->url();
		} );

		$this->router = $r;
		$this->url = $url;

		Framework::set( 'redirect', $redirector );

		Framework::set( 'Psr\Http\Message\ServerRequestInterface', ( new DiactorosFactory() )->createRequest( $request ) );

		Framework::set( 'Psr\Http\Message\ResponseInterface', new PsrResponse() );

		Framework::set( 'http.factory', new ResponseFactory( Framework::get( 'view.factory' ), $redirector ) );


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

	public function cookieJar()
	{
		return $this->cookies;
	}

	public function routeRequest()
	{
		$this->addMiddleware( [
			new EncryptCookies( Framework::get( 'encrypter' ) ),
			new StartSession( Framework::get( 'session.mgr' ) ),
		] );

		$this->router->getRoutes()->refreshNameLookups();

		$this->response = ( new Pipeline() )->withExceptionHandler( function ( $request, $e )
		{
			$handler = Framework::exceptionHandler();

			$handler->report( $e );
			return $handler->render( $request, $e );
		} )->send( $this->request )->through( $this->middleware )->then( function ( $request )
		{
			return $this->router->dispatch( $request );
		} );

		Framework::set( 'http.response', $this->response );

		return $this->response;
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
}
