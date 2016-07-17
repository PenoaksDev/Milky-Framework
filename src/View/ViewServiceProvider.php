<?php
namespace Penoaks\View;

use Penoaks\Barebones\ServiceProvider;
use Penoaks\View\Compilers\BladeCompiler;
use Penoaks\View\Engines\CompilerEngine;
use Penoaks\View\Engines\EngineResolver;
use Penoaks\View\Engines\PhpEngine;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class ViewServiceProvider extends ServiceProvider
{
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerEngineResolver();

		$this->registerViewFinder();

		$this->registerFactory();
	}

	/**
	 * Register the engine resolver instance.
	 *
	 * @return void
	 */
	public function registerEngineResolver()
	{
		$this->bindings->singleton( 'view.engine.resolver', function ()
		{
			$resolver = new EngineResolver;

			// Next we will register the various engines with the resolver so that the
			// environment can resolve the engines it needs for various views based
			// on the extension of view files. We call a method for each engines.
			foreach ( ['php', 'blade'] as $engine )
			{
				$this->{'register' . ucfirst( $engine ) . 'Engine'}( $resolver );
			}

			return $resolver;
		} );
	}

	/**
	 * Register the PHP engine implementation.
	 *
	 * @param  \Penoaks\View\Engines\EngineResolver $resolver
	 * @return void
	 */
	public function registerPhpEngine( $resolver )
	{
		$resolver->register( 'php', function ()
		{
			return new PhpEngine;
		} );
	}

	/**
	 * Register the Blade engine implementation.
	 *
	 * @param  \Penoaks\View\Engines\EngineResolver $resolver
	 * @return void
	 */
	public function registerBladeEngine( $resolver )
	{
		$bindings = $this->fw;

		// The Compiler engine requires an instance of the CompilerInterface, which in
		// this case will be the Blade compiler, so we'll first create the compiler
		// instance to pass into the engine so it can compile the views properly.
		$bindings->singleton( 'blade.compiler', function ( $bindings )
		{
			$cache = $bindings['config']['view.compiled'];

			return new BladeCompiler( $bindings['files'], $cache );
		} );

		$resolver->register( 'blade', function () use ( $bindings )
		{
			return new CompilerEngine( $bindings['blade.compiler'] );
		} );
	}

	/**
	 * Register the view finder implementation.
	 *
	 * @return void
	 */
	public function registerViewFinder()
	{
		$this->bindings->bind( 'view.finder', function ( $bindings )
		{
			$paths = $bindings['config']['view.paths'];

			return new FileViewFinder( $bindings['files'], $paths );
		} );
	}

	/**
	 * Register the view environment.
	 *
	 * @return void
	 */
	public function registerFactory()
	{
		$this->bindings->singleton( 'view', function ( $bindings )
		{
			// Next we need to grab the engine resolver instance that will be used by the
			// environment. The resolver will be used by an environment to get each of
			// the various engine implementations such as plain PHP or Blade engine.
			$resolver = $bindings['view.engine.resolver'];

			$finder = $bindings['view.finder'];

			$env = new Factory( $resolver, $finder, $bindings['events'] );

			// We will also set the bindings instance on this view environment since the
			// view composers may be classes registered in the bindings, which allows
			// for great testable, flexible composers for the application developer.
			$env->setBindings( $bindings );

			$env->share( 'fw', $bindings->get( 'fw' ) );

			return $env;
		} );
	}
}
