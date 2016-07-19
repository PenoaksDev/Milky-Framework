<?php
namespace Penoaks\Providers;

use Penoaks\Barebones\ServiceProvider;
use Penoaks\Bindings\Bindings;
use Penoaks\Contracts\Validation\ValidatesWhenResolved;
use Penoaks\Http\FormRequest;
use Penoaks\Routing\Redirector;
use Symfony\Component\HttpFoundation\Request;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class FoundationServiceProvider extends ServiceProvider
{
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		//
	}

	/**
	 * Bootstrap the application services.
	 *
	 * @param Bindings $bindings
	 */
	public function boot( $bindings )
	{
		$bindings->afterResolving( function ( ValidatesWhenResolved $resolved )
		{
			$resolved->validate();
		} );

		$bindings->resolving( function ( FormRequest $request, Bindings $bindings )
		{
			$this->initializeRequest( $request, $bindings->get( 'request' ) );

			$request->setBindings( $bindings )->setRedirector( $bindings->make( Redirector::class ) );
		} );
	}

	/**
	 * Initialize the form request with data from the given request.
	 *
	 * @param  FormRequest $form
	 * @param  Request $current
	 * @return void
	 */
	protected function initializeRequest( FormRequest $form, Request $current )
	{
		$files = $current->files->all();

		$files = is_array( $files ) ? array_filter( $files ) : $files;

		$form->initialize( $current->query->all(), $current->request->all(), $current->attributes->all(), $current->cookies->all(), $files, $current->server->all(), $current->getContent() );

		if ( $session = $current->getSession() )
		{
			$form->setSession( $session );
		}

		$form->setUserResolver( $current->getUserResolver() );

		$form->setRouteResolver( $current->getRouteResolver() );
	}
}
