<?php namespace Milky\Exceptions;

use Milky\Binding\ServiceResolver;
use Milky\Exceptions\Displayers\HtmlDisplayer;
use Milky\Exceptions\Filters\VerboseFilter;
use Milky\Facades\Config;

/**
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class ExceptionsServiceResolver extends ServiceResolver
{
	private $handlerInstance;
	private $identifierInstance;
	private $infoInstance;

	public function __construct()
	{
		$this->setDefault( 'handler' );

		$this->addClassAlias( Handler::class, 'handler' );
		$this->addClassAlias( ExceptionIdentifier::class, 'identifier' );
		$this->addClassAlias( ExceptionInfo::class, 'info' );
		$this->addClassAlias( HtmlDisplayer::class, 'displayer' );
		$this->addClassAlias( VerboseFilter::class, 'filter' );
	}

	public function handler()
	{
		return $this->handlerInstance ?: $this->handlerInstance = Handler::build();
	}

	public function identifier()
	{
		return $this->identifierInstance ?: $this->identifierInstance = new ExceptionIdentifier();
	}

	public function info()
	{
		return $this->infoInstance ?: $this->infoInstance = new ExceptionInfo( realpath( __DIR__ . '/../../../resources/errors.json' ) );
	}

	public function displayer()
	{
		return new HtmlDisplayer( $this->info(), realpath( __DIR__ . '/../../../resources/error.html' ) );
	}

	public function filter()
	{
		return new VerboseFilter( Config::get( 'app.debug' ), false );
	}

	public function key()
	{
		return 'exceptions';
	}
}
