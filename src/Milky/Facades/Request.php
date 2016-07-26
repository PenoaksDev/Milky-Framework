<?php namespace Milky\Facades;

use Milky\Framework;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class Request extends BaseFacade
{
	protected function __getResolver()
	{
		return Framework::get( 'http.factory' )->request();
	}
}
