<?php namespace Milky\Providers;

use Illuminate\Contracts\Routing\UrlGenerator;
use Milky\Http\Routing\Router;
use Penoaks\Facades\Bindings;

/*
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
abstract class RouteServiceProvider extends ServiceProvider
{
	/**
	 * The controller namespace for the application.
	 *
	 * @var string|null
	 */
	protected $namespace;

	/**
	 * Bootstrap any application services.
	 *
	 * @param  Router $router
	 */
	public function boot()
	{
		$router = Bindings::make( 'router' );

		$this->setRootControllerNamespace();

		$this->map( $router );

		$this->app->booted( function () use ( $router )
		{
			$router->getRoutes()->refreshNameLookups();
		} );
	}

	abstract public function map( Router &$r );

	/**
	 * Set the root controller namespace for the application.
	 *
	 */
	protected function setRootControllerNamespace()
	{
		if ( is_null( $this->namespace ) )
			return;

		Bindings::get( UrlGenerator::class )->setRootControllerNamespace( $this->namespace );
	}

	/**
	 * Load the standard routes file for the application.
	 *
	 * @param  string $path
	 * @return mixed
	 */
	protected function loadRoutesFrom( $path )
	{
		$router = Bindings::make( Router::class );

		if ( is_null( $this->namespace ) )
			return require $path;

		$router->group( ['namespace' => $this->namespace], function ( Router $router ) use ( $path )
		{
			require $path;
		} );
	}

	/**
	 * Register the service provider.
	 *
	 */
	public function register()
	{
		//
	}

	/**
	 * Pass dynamic methods onto the router instance.
	 *
	 * @param  string $method
	 * @param  array $parameters
	 * @return mixed
	 */
	public function __call( $method, $parameters )
	{
		return call_user_func_array( [Bindings::get( 'router' ), $method], $parameters );
	}
}
