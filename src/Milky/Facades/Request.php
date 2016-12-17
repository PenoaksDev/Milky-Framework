<?php namespace Milky\Facades;

use Milky\Http\HttpFactory;

/**
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class Request extends BaseFacade
{
	protected function __getResolver()
	{
		return HttpFactory::i()->request();
	}

	public static function ajax()
	{
		return static::__do( __FUNCTION__, [] );
	}

	public static function pjax()
	{
		return static::__do( __FUNCTION__, [] );
	}
}
