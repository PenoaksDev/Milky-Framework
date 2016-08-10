<?php

/*
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */

namespace Milky\Http\View\Compilers;

use League\CommonMark\Converter;
use Milky\Filesystem\Filesystem;

class MarkdownCompiler extends Compiler implements CompilerInterface
{
	/**
	 * The markdown instance.
	 *
	 * @var Converter
	 */
	protected $markdown;

	/**
	 * Create a new instance.
	 *
	 * @param Converter $markdown
	 * @param Filesystem $files
	 * @param string $cachePath
	 *
	 * @return void
	 */
	public function __construct( Converter $markdown, Filesystem $files, $cachePath )
	{
		parent::__construct( $files, $cachePath );
		$this->markdown = $markdown;
	}

	/**
	 * Compile the view at the given path.
	 *
	 * @param string $path
	 *
	 * @return void
	 */
	public function compile( $path )
	{
		$contents = $this->markdown->convertToHtml( $this->files->get( $path ) );
		$this->files->put( $this->getCompiledPath( $path ), $contents );
	}

	/**
	 * Return the filesystem instance.
	 *
	 * @return Filesystem
	 */
	public function getFiles()
	{
		return $this->files;
	}

	/**
	 * Return the markdown instance.
	 *
	 * @return Converter
	 */
	public function getMarkdown()
	{
		return $this->markdown;
	}
}
