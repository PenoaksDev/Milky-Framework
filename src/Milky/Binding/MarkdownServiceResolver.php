<?php

/*
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */

namespace Milky\Binding;

use League\CommonMark\Converter;
use League\CommonMark\DocParser;
use League\CommonMark\Environment;
use League\CommonMark\HtmlRenderer;
use Milky\Facades\Config;
use Milky\Filesystem\Filesystem;
use Milky\Helpers\Arr;
use Milky\Http\View\Compilers\MarkdownCompiler;

class MarkdownServiceResolver extends ServiceResolver
{
	protected $environmentInstance;

	protected $markdownInstance;

	protected $compilerInstance;

	public function __construct()
	{
		$this->setDefault( 'markdown' );

		$this->addClassAlias( Environment::class, 'environment' );
		$this->addClassAlias( Converter::class, 'markdown' );
		$this->addClassAlias( MarkdownCompiler::class, 'compiler' );
	}

	public function environment()
	{
		if ( is_null( $this->environmentInstance ) )
		{
			$config = Config::get( 'markdown' );
			$env = Environment::createCommonMarkEnvironment();

			$env->mergeConfig( array_except( $config, ['extensions', 'views'] ) );

			foreach ( Arr::get( $config, 'extensions' ) as $extension )
				$env->addExtension( UniversalBuilder::resolve( $extension ) );

			$this->environmentInstance = $env;
		}

		return $this->environmentInstance;
	}

	public function markdown()
	{
		return $this->markdownInstance ?: $this->markdownInstance = new Converter( new DocParser( $this->environment() ), new HtmlRenderer( $this->environment() ) );
	}

	public function compiler()
	{
		return $this->compilerInstance ?: $this->compilerInstance = new MarkdownCompiler( $this->markdown(), Filesystem::i(), Config::get( 'view.compiled' ) );
	}

	public function key()
	{
		return 'markdown';
	}
}
