<?php

/*
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */

namespace Milky\Http\View\Compilers;

use League\CommonMark\Converter;
use Milky\Http\View\Engines\PhpEngine;

class PhpMarkdownEngine extends PhpEngine
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
	 *
	 * @return void
	 */
	public function __construct( Converter $markdown )
	{
		$this->markdown = $markdown;
	}

	/**
	 * Get the evaluated contents of the view.
	 *
	 * @param string $path
	 * @param array $data
	 *
	 * @return string
	 */
	public function get( $path, array $data = [] )
	{
		$contents = parent::get( $path, $data );

		return $this->markdown->convertToHtml( $contents );
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
