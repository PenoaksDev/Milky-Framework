<?php namespace Milky\Http;

use Milky\Framework;
use Milky\Http\Cookies\CookieJar;
use Milky\Http\Middleware\EncryptCookies;
use Milky\Http\Routing\Router;
use Milky\Http\Session\Middleware\StartSession;
use Milky\Pipeline\Pipeline;

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
	 * @var array
	 */
	private $middleware = [];

	public function __construct( Framework $fw )
	{
		$this->fw = $fw;

		$this->router = new Router( null );

		$config = $fw->config['config']['session'];
		$this->cookies = ( new CookieJar() )->setDefaultPathAndDomain( $config['path'], $config['domain'], $config['secure'] );
	}

	public function router()
	{
		return $this->router;
	}

	public function routeRequest( $request = null )
	{
		if ( !$request )
			$request = Request::capture();

		$this->addMiddleware( [
			new EncryptCookies( Framework::get( 'encrypter' ) ),
			new StartSession( Framework::get( 'session' ) ),
		] );

		$response = ( new Pipeline() )->withExceptionHandler( function ( $passable, $e )
		{
			throw $e; // TEMP
		} )->send( $request )->through( $this->middleware )->then( function ( $request )
		{
			return $this->router->dispatch( $request );
		} );

		return $response;
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
