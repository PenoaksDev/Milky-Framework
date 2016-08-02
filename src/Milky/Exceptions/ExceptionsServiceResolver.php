<?php namespace Milky\Exceptions;

use Milky\Binding\Resolvers\ServiceResolver;
use Milky\Exceptions\Displayers\HtmlDisplayer;
use Milky\Exceptions\Filters\VerboseFilter;
use Milky\Facades\Config;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class ExceptionsServiceResolver extends ServiceResolver
{
	protected $handler;
	protected $identifier;
	protected $info;

	public function __construct()
	{
		$this->handler = Handler::build();

		$this->identifier = new ExceptionIdentifier();
		$this->info = new ExceptionInfo( realpath( __DIR__ . '/../../../resources/errors.json' ) );

		$this->addClassAlias( Handler::class, 'mgr' );
		$this->addClassAlias( ExceptionIdentifier::class, 'identifier' );
		$this->addClassAlias( ExceptionInfo::class, 'info' );
		$this->addClassAlias( HtmlDisplayer::class, 'displayer' );
		$this->addClassAlias( VerboseFilter::class, 'filter' );

		$this->setDefault( 'handler' );
	}

	public function displayer()
	{
		return new HtmlDisplayer( $this->info, realpath( __DIR__ . '/../../../resources/error.html' ) );
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
