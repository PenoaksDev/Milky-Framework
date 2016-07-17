<?php
namespace Penoaks\Facades;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class Events extends BaseFacade
{
	protected function __getResolver()
	{
		/* Implements the Events Dispatcher */
		return 'events';
	}

	public static function fire( $event, $payload = [], $halt = false )
	{
		static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}

	public static function listenEvents( $listener, $priority = 0 )
	{
		static::__do( __FUNCTION__, args_with_keys( func_get_args(), __CLASS__, __FUNCTION__ ) );
	}
}
