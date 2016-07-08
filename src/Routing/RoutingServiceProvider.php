<?php
namesapce Penoaks\Routing;

use Foundation\Framework;
use Foundation\Barebones\ServiceProvider;
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
		$this->fw->bindings['router'] = $this->fw->bindings->share( function ( $fw )
		{
			return new Router( $fw->bindings['events'], $fw );
		} );
	}

	/**
	 * Register the URL generator service.
	 *
	 * @return void
	 */
	protected function registerUrlGenerator()
	{
		$this->fw->bindings['url'] = $this->fw->bindings->share( function ( $fw )
		{
			$routes = $fw->bindings['router']->getRoutes();

			// The URL generator needs the route collection that exists on the router.
			// Keep in mind this is an object, so we're passing by references here
			// and all the registered routes will be available to the generator.
			$fw->bindings->instance( 'routes', $routes );

			$url = new UrlGenerator( $routes, $fw->rebinding( 'request', $this->requestRebinder() ) );

			$url->setSessionResolver( function ()
			{
				return $this->fw->bindings['session'];
			} );

			// If the route collection is "rebound", for example, when the routes stay
			// cached for the application, we will need to rebind the routes on the
			// URL generator instance so it has the latest version of the routes.
			$fw->rebinding( 'routes', function ( $fw, $routes )
			{
				$fw->bindings['url']->setRoutes( $routes );
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
		return function ( $fw, $request )
		{
			$fw->bindings['url']->setRequest( $request );
		};
	}

	/**
	 * Register the Redirector service.
	 *
	 * @return void
	 */
	protected function registerRedirector()
	{
		$this->fw->bindings['redirect'] = $this->fw->bindings->share( function ( $fw )
		{
			$redirector = new Redirector( $fw->bindings['url'] );

			// If the session is set on the application instance, we'll inject it into
			// the redirector instance. This allows the redirect responses to allow
			// for the quite convenient "with" methods that flash to the session.
			if ( isset( $fw->bindings['session.store'] ) )
			{
				$redirector->setSession( $fw->bindings['session.store'] );
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
		$this->fw->bindings->bind( 'Psr\Http\Message\ServerRequestInterface', function ( $fw )
		{
			return ( new DiactorosFactory )->createRequest( $fw->make( 'request' ) );
		} );
	}

	/**
	 * Register a binding for the PSR-7 response implementation.
	 *
	 * @return void
	 */
	protected function registerPsrResponse()
	{
		$this->fw->bindings->bind( 'Psr\Http\Message\ResponseInterface', function ( $fw )
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
		$this->fw->bindings->singleton( 'Penoaks\Contracts\Routing\ResponseFactory', function ( $fw )
		{
			return new ResponseFactory( $fw->bindings['Penoaks\Contracts\View\Factory'], $fw->bindings['redirect'] );
		} );
	}
}
