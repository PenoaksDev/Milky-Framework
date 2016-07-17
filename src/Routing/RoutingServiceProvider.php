<?php
namespace Penoaks\Routing;

use Penoaks\Barebones\ServiceProvider;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Zend\Diactoros\Response as PsrResponse;

class RoutingServiceProvider extends ServiceProvider
{
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerRouter();

		$this->registerUrlGenerator();

		$this->registerRedirector();

		$this->registerPsrRequest();

		$this->registerPsrResponse();

		$this->registerResponseFactory();
	}

	/**
	 * Register the router instance.
	 *
	 * @return void
	 */
	protected function registerRouter()
	{
		$this->bindings['router'] = $this->bindings->share( function ( $bindings )
		{
			return new Router( $bindings['events'], $bindings );
		} );
	}

	/**
	 * Register the URL generator service.
	 *
	 * @return void
	 */
	protected function registerUrlGenerator()
	{
		$this->bindings['url'] = $this->bindings->share( function ( $bindings )
		{
			$routes = $bindings['router']->getRoutes();

			// The URL generator needs the route collection that exists on the router.
			// Keep in mind this is an object, so we're passing by references here
			// and all the registered routes will be available to the generator.
			$bindings->instance( 'routes', $routes );

			$url = new UrlGenerator( $routes, $bindings->rebinding( 'request', $this->requestRebinder() ) );

			$url->setSessionResolver( function ()
			{
				return $this->bindings['session'];
			} );

			// If the route collection is "rebound", for example, when the routes stay
			// cached for the application, we will need to rebind the routes on the
			// URL generator instance so it has the latest version of the routes.
			$bindings->rebinding( 'routes', function ( $bindings, $routes )
			{
				$bindings['url']->setRoutes( $routes );
			} );

			return $url;
		} );
	}

	/**
	 * Get the URL generator request rebinder.
	 *
	 * @return \Closure
	 */
	protected function requestRebinder()
	{
		return function ( $bindings, $request )
		{
			$bindings['url']->setRequest( $request );
		};
	}

	/**
	 * Register the Redirector service.
	 *
	 * @return void
	 */
	protected function registerRedirector()
	{
		$this->bindings['redirect'] = $this->bindings->share( function ( $bindings )
		{
			$redirector = new Redirector( $bindings['url'] );

			// If the session is set on the application instance, we'll inject it into
			// the redirector instance. This allows the redirect responses to allow
			// for the quite convenient "with" methods that flash to the session.
			if ( isset( $bindings['session.store'] ) )
			{
				$redirector->setSession( $bindings['session.store'] );
			}

			return $redirector;
		} );
	}

	/**
	 * Register a binding for the PSR-7 request implementation.
	 *
	 * @return void
	 */
	protected function registerPsrRequest()
	{
		$this->bindings->bind( 'Psr\Http\Message\ServerRequestInterface', function ( $bindings )
		{
			return ( new DiactorosFactory )->createRequest( $bindings->make( 'request' ) );
		} );
	}

	/**
	 * Register a binding for the PSR-7 response implementation.
	 *
	 * @return void
	 */
	protected function registerPsrResponse()
	{
		$this->bindings->bind( 'Psr\Http\Message\ResponseInterface', function ( $bindings )
		{
			return new PsrResponse();
		} );
	}

	/**
	 * Register the response factory implementation.
	 *
	 * @return void
	 */
	protected function registerResponseFactory()
	{
		$this->bindings->singleton( 'Penoaks\Contracts\Routing\ResponseFactory', function ( $bindings )
		{
			return new ResponseFactory( $bindings['Penoaks\Contracts\View\Factory'], $bindings['redirect'] );
		} );
	}
}
