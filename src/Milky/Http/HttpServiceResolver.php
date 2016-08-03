<?php namespace Milky\Http;

use Milky\Binding\Resolvers\ServiceResolver;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class HttpServiceResolver extends ServiceResolver
{
	public function __construct()
	{
		$this->addClassAlias( HttpFactory::class, 'factory' );
		$this->addClassAlias( Request::class, 'request' );
		$this->addClassAlias( Response::class, 'response' );
	}

	public function factory()
	{
		return HttpFactory::i();
	}

	public function request()
	{
		return HttpFactory::i()->request();
	}

	public function response()
	{
		return HttpFactory::i()-$this->response();
	}

	public function key()
	{
		return 'http';
	}
}
