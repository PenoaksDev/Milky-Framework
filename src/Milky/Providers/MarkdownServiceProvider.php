<?php

/*
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */

namespace Milky\Providers;

use League\CommonMark\Converter;
use League\CommonMark\Environment;
use Milky\Binding\MarkdownServiceResolver;
use Milky\Binding\UniversalBuilder;
use Milky\Config\Builder\ConfigurationBuilder;
use Milky\Config\ConfigurationLoader;
use Milky\Facades\Config;
use Milky\Facades\View;
use Milky\Http\View\Compilers\MarkdownCompiler;
use Milky\Http\View\Compilers\PhpMarkdownEngine;
use Milky\Http\View\Engines\BladeMarkdownEngine;
use Milky\Http\View\Engines\CompilerEngine;
use Milky\Http\View\Engines\EngineResolver;

class MarkdownServiceProvider extends ServiceProvider
{
	public function boot()
	{
		if ( !Config::has( 'markdown' ) )
		{
			$md = new ConfigurationBuilder( 'markdown' );

			$md->add( 'views', true )->withComment( [
				"This option specifies if the view integration is enabled so you can write",
				"markdown views and have them rendered as html. The following extensions",
				"are currently supported: \".md\", \".md.php\", and \".md.blade.php\". You may",
				"disable this integration if it is conflicting with another package.",
			], "Enable View Integration" );

			$md->add( 'extensions', [] )->withComment( [
				"This option specifies what extensions will be automatically enabled.",
				"Simply provide your extension class names here.",
			], "Extenstions" );

			$md->add( 'renderer', [
				'block_separator' => '\n',
				'inner_separator' => '\n',
				'soft_break' => '\n',
			] )->withComment( [
				"This option specifies an array of options for rendering HTML."
			], "Renderer Configuration" );

			$md->add( 'enable_em', true )->withComment( [
				"This option specifies if `<em>` parsing is enabled."
			], "Enable Em Tag Parsing" );

			$md->add( 'enable_strong', true )->withComment( [
				"This option specifies if `<strong>` parsing is enabled."
			], "Enable Strong Tag Parsing" );

			$md->add( 'use_asterisk', true )->withComment( [
				"This option specifies if `*` should be parsed for emphasis."
			], "Enable Asterisk Parsing" );

			$md->add( 'use_underscore', true )->withComment( [
				"This option specifies if `_` should be parsed for emphasis."
			], "Enable Underscore Parsing" );

			$md->add( 'safe', false )->withComment( [
				"This option specifies if raw HTML is rendered in the document. Setting",
				"this to true will not render HTML, and false will."
			], "Safe Mode" );

			ConfigurationLoader::create( $md );
		}

		UniversalBuilder::registerResolver( new MarkdownServiceResolver() );

		if ( Config::get( 'markdown.views' ) )
		{
			$markdown = UniversalBuilder::resolveClass( Converter::class );
			$compiler = UniversalBuilder::resolveClass( MarkdownCompiler::class );

			EngineResolver::i()->register( 'md', function () use ( $compiler )
			{
				return new CompilerEngine( $compiler );
			} );

			EngineResolver::i()->register( 'phpmd', function () use ( $markdown )
			{
				return new PhpMarkdownEngine( $markdown );
			});

			EngineResolver::i()->register( 'blademd', function () use ( $compiler, $markdown )
			{
				return new BladeMarkdownEngine( $compiler, $markdown );
			});

			View::addExtension( 'md', 'md' );
			View::addExtension( 'md.php', 'phpmd' );
			View::addExtension( 'md.blade.php', 'blademd' );
		}
	}
}
