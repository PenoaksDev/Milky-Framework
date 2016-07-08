<?php

namespace Penoaks\View;

use Penoaks\View\Engines\PhpEngine;
use Penoaks\Support\ServiceProvider;
use Penoaks\View\Engines\CompilerEngine;
use Penoaks\View\Engines\EngineResolver;
use Penoaks\View\Compilers\BladeCompiler;

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
		$this->fw->bindings->singleton('view.engine.resolver', function ()
{
			$resolver = new EngineResolver;

			// Next we will register the various engines with the resolver so that the
			// environment can resolve the engines it needs for various views based
			// on the extension of view files. We call a method for each engines.
			foreach (['php', 'blade'] as $engine)
{
				$this->{'register'.ucfirst($engine).'Engine'}($resolver);
			}

			return $resolver;
		});
	}

	/**
	 * Register the PHP engine implementation.
	 *
	 * @param  \Penoaks\View\Engines\EngineResolver  $resolver
	 * @return void
	 */
	public function registerPhpEngine($resolver)
	{
		$resolver->register('php', function ()
{
			return new PhpEngine;
		});
	}

	/**
	 * Register the Blade engine implementation.
	 *
	 * @param  \Penoaks\View\Engines\EngineResolver  $resolver
	 * @return void
	 */
	public function registerBladeEngine($resolver)
	{
		$fw = $this->fw;

		// The Compiler engine requires an instance of the CompilerInterface, which in
		// this case will be the Blade compiler, so we'll first create the compiler
		// instance to pass into the engine so it can compile the views properly.
		$fw->singleton('blade.compiler', function ($fw)
{
			$cache = $fw->bindings['config']['view.compiled'];

			return new BladeCompiler($fw->bindings['files'], $cache);
		});

		$resolver->register('blade', function () use ($fw)
{
			return new CompilerEngine($fw->bindings['blade.compiler']);
		});
	}

	/**
	 * Register the view finder implementation.
	 *
	 * @return void
	 */
	public function registerViewFinder()
	{
		$this->fw->bindings->bind('view.finder', function ($fw)
{
			$paths = $fw->bindings['config']['view.paths'];

			return new FileViewFinder($fw->bindings['files'], $paths);
		});
	}

	/**
	 * Register the view environment.
	 *
	 * @return void
	 */
	public function registerFactory()
	{
		$this->fw->bindings->singleton('view', function ($fw)
{
			// Next we need to grab the engine resolver instance that will be used by the
			// environment. The resolver will be used by an environment to get each of
			// the various engine implementations such as plain PHP or Blade engine.
			$resolver = $fw->bindings['view.engine.resolver'];

			$finder = $fw->bindings['view.finder'];

			$env = new Factory($resolver, $finder, $fw->bindings['events']);

			// We will also set the bindings instance on this view environment since the
			// view composers may be classes registered in the bindings, which allows
			// for great testable, flexible composers for the application developer.
			$env->setBindings($fw);

			$env->share('fw', $fw);

			return $env;
		});
	}
}
