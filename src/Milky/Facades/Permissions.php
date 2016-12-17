<?php namespace Milky\Facades;

use Milky\Account\Models\PermissibleEntity;
use Milky\Account\Permissions\PermissibleModel;
use Milky\Account\Permissions\PermissionManager;

/**
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class Permissions extends BaseFacade
{
	protected function __getResolver()
	{
		return PermissionManager::class;
	}

	public static function check( $namespace, PermissibleModel $model = null, PermissibleEntity $entity = null )
	{
		return static::__do( __FUNCTION__, compact( 'namespace', 'model', 'entity' ) );
	}
}
