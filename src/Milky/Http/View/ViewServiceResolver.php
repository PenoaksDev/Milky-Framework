<?php namespace Milky\Http\View;

use Milky\Binding\ServiceResolver;
use Milky\Facades\Config;
use Milky\Filesystem\Filesystem;
use Milky\Http\View\Compilers\BladeCompiler;
use Milky\Http\View\Engines\CompilerEngine;
use Milky\Http\View\Engines\EngineResolver;
use Milky\Http\View\Engines\PhpEngine;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class ViewServiceResolver extends ServiceResolver
{
	/**
	 * @var EngineResolver
	 */
	protected $engineResolverInstance;

	/**
	 * @var BladeCompiler
	 */
	protected $bladeCompilerInstance;

	/**
	 * @var FileViewFinder
	 */
	protected $finderInstance;

	/**
	 * @var ViewFactory
	 */
	protected $factoryInstance;

	public function __construct()
	{
		$this->setDefault( 'factory' );

		$this->addClassAlias( EngineResolver::class, 'engineResolver' );
		$this->addClassAlias( BladeCompiler::class, 'bladeCompiler' );
		$this->addClassAlias( FileViewFinder::class, 'finder' );
		$this->addClassAlias( ViewFactory::class, 'factory' );
	}

	/**
	 * @return EngineResolver
	 */
	public function engineResolver()
	{
		if ( is_null( $this->engineResolverInstance ) )
		{
			$this->engineResolverInstance = new EngineResolver();

			$this->engineResolverInstance->register( 'php', function ()
			{
				return new PhpEngine();
			} );
		}

		return $this->engineResolverInstance;
	}

	/**
	 * @return BladeCompiler
	 */
	public function bladeCompiler()
	{
		if ( is_null( $this->bladeCompilerInstance ) )
		{
			// The Compiler engine requires an instance of the CompilerInterface, which in
			// this case will be the Blade compiler, so we'll first create the compiler
			// instance to pass into the engine so it can compile the views properly.
			$this->bladeCompilerInstance = new BladeCompiler( Filesystem::i(), Config::get( 'view.compiled' ) );

			$this->engineResolver()->register( 'blade', function ()
			{
				return new CompilerEngine( $this->bladeCompilerInstance );
			} );
		}

		return $this->bladeCompilerInstance;
	}

	/**
	 * @return FileViewFinder
	 */
	public function finder()
	{
		return $this->finderInstance ?: $this->finderInstance = new FileViewFinder( Filesystem::i(), Config::get( 'view.paths' ) );
	}

	/**
	 * @return ViewFactory
	 */
	public function factory()
	{
		return $this->factoryInstance ?: $this->factoryInstance = new ViewFactory( $this->engineResolver(), $this->finder() );
	}

	public function key()
	{
		return 'view';
	}
}
