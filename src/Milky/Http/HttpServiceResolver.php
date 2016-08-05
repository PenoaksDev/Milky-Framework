<?php namespace Milky\Http;

use Milky\Binding\Resolvers\ServiceResolver;
use Milky\Facades\Config;
use Milky\Http\Cookies\CookieJar;
use Milky\Http\Routing\Redirector;
use Milky\Http\Routing\ResponseFactory;
use Milky\Http\Routing\Router;
use Milky\Http\Routing\UrlGenerator;
use Milky\Http\Session\Drivers\SessionDriver;
use Milky\Http\Session\SessionManager;
use Milky\Http\View\ViewFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
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
class HttpServiceResolver extends ServiceResolver
{
	protected $factoryInstance;
	protected $cookieJarInstance;
	protected $sessionMgrInstance;

	private $responseFactoryInstance;

	private $serverRequestInstance;
	private $psrResponseInstance;

	public function __construct()
	{
		$this->addClassAlias( HttpFactory::class, 'factory' );
		$this->addClassAlias( Redirector::class, 'redirector' );
		$this->addClassAlias( Router::class, 'router' );
		$this->addClassAlias( UrlGenerator::class, 'url' );
		$this->addClassAlias( Request::class, 'request' );
		$this->addClassAlias( Response::class, 'response' );

		$this->addClassAlias( CookieJar::class, 'cookieJar' );

		$this->addClassAlias( SessionManager::class, 'sessionMgr' );
		$this->addClassAlias( SessionDriver::class, 'sessionDriver' );

		$this->addClassAlias( ResponseFactory::class, 'responseFactory' );

		$this->addClassAlias( ServerRequestInterface::class, 'serverRequest' );
		$this->addClassAlias( ResponseInterface::class, 'psrRequest' );

		$this->setDefault( 'factory' );
	}

	/**
	 * @return HttpFactory
	 */
	public function factory()
	{
		return $this->factoryInstance ?: $this->factoryInstance = HttpFactory::build();
	}

	/**
	 * @return Redirector
	 */
	public function redirector()
	{
		return $this->factory()->redirector();
	}

	/**
	 * @return Router
	 */
	public function router()
	{
		return $this->factory()->router();
	}

	/**
	 * @return UrlGenerator
	 */
	public function url()
	{
		return $this->factory()->url();
	}

	/**
	 * @return Request
	 */
	public function request()
	{
		return $this->factory()->request();
	}

	/**
	 * @return Response
	 */
	public function response()
	{
		return $this->factory()->response();
	}

	/**
	 * @return CookieJar
	 */
	public function cookieJar()
	{
		if ( is_null( $this->cookieJarInstance ) )
		{
			$config = Config::get( 'session' );
			$this->cookieJarInstance = CookieJar::build()->setDefaultPathAndDomain( $config['path'], $config['domain'], $config['secure'] );
		}

		return $this->cookieJarInstance;
	}

	/**
	 * @return SessionManager
	 */
	public function sessionMgr()
	{
		return $this->sessionMgrInstance ?: $this->sessionMgrInstance = new SessionManager();
	}

	/**
	 * @return Session\Store
	 */
	public function sessionDriver()
	{
		return $this->sessionMgr()->driver();
	}

	public function key()
	{
		return 'http';
	}

	public function responseFactory()
	{
		return $this->responseFactoryInstance ?: $this->responseFactoryInstance = new ResponseFactory( ViewFactory::i(), $this->redirector() );
	}

	public function serverRequest()
	{
		return $this->serverRequestInstance ?: $this->serverRequestInstance = ( new DiactorosFactory() )->createRequest( $this->request() );
	}

	public function psrResponse()
	{
		return $this->psrResponseInstance ?: $this->psrResponseInstance = new PsrResponse();
	}
}
