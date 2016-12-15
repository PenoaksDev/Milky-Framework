<?php

/*
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */

namespace Milky\Facades;

use Milky\Account\Permissions\PermissionManager;

/**
 * Class Permission
 */
class Permissions extends BaseFacade
{
	protected function __getResolver()
	{
		return PermissionManager::class;
	}

	public static function checkPolicies( $namespace, $entity )
	{
		return static::__do( __FUNCTION__, compact( 'namespace', 'entity' ) );
	}
}
