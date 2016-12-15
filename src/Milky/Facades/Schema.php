<?php namespace Milky\Facades;

use Milky\Binding\UniversalBuilder;

/**
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class Schema extends BaseFacade
{
	protected function __getResolver()
	{
		return UniversalBuilder::resolve( 'db.connection' )->getSchemaBuilder();
	}
}
