<?php namespace Milky\Http\View;

use Milky\Binding\Resolvers\ServiceResolver;
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
	protected $engineResolver;
	protected $bladeCompiler;
	protected $finder;
	protected $factory;

	public function __construct()
	{
		$this->engineResolver = new EngineResolver();

		$this->engineResolver->register( 'php', function ()
		{
			return new PhpEngine();
		} );

		// The Compiler engine requires an instance of the CompilerInterface, which in
		// this case will be the Blade compiler, so we'll first create the compiler
		// instance to pass into the engine so it can compile the views properly.
		$this->bladeCompiler = new BladeCompiler( Filesystem::i(), Config::get( 'view.compiled' ) );

		$this->engineResolver->register( 'blade', function ()
		{
			return new CompilerEngine( $this->bladeCompiler );
		} );

		$this->finder = new FileViewFinder( Filesystem::i(), Config::get( 'view.paths' ) );

		$this->factory = new Factory( $this->engineResolver, $this->finder );

		$this->addClassAlias( EngineResolver::class, 'engineResolver' );
		$this->addClassAlias( BladeCompiler::class, 'bladeCompiler' );
		$this->addClassAlias( FileViewFinder::class, 'finder' );
		$this->addClassAlias( Factory::class, 'factory' );
	}

	public function key()
	{
		return 'view';
	}
}
