<?php namespace Milky\Account\Permissions;

use Milky\Services\ServiceFactory;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class PermissionManager extends ServiceFactory
{
	/**
	 * Are operator users allowed? TODO CONFIG
	 *
	 * @var bool
	 */
	private $allowOps = true;

	/**
	 * The loaded permission nodes.
	 *
	 * @var array
	 */
	protected $loadedPermissions = [];

	/**
	 * Adds a new policy checker.
	 *
	 * Policies are checked for permissions before they are checked by the general permission backend
	 *
	 * @param Policy $policy
	 */
	public static function policy( $policy )
	{

	}

	public function has( $permission )
	{

	}

	/**
	 *
	 *
	 * @param string $permission
	 */
	public function parseNode( $permission )
	{
		// Everyone
		if ( $permission == null || empty( $permission.isEmpty() ) || $permission == "-1" || $permission == "everybody" || $permission == "everyone" )
			$permission = PermissionDefault.EVERYBODY.getNameSpace();

		// OP Only
		if ( $permission.equals( "0" ) || $permission.equalsIgnoreCase( "op" ) || $permission.equalsIgnoreCase( "root" ) )
			$permission = PermissionDefault.OP.getNameSpace();

		if ( $permission.equalsIgnoreCase( "admin" ) )
			$permission = PermissionDefault.ADMIN.getNameSpace();

		return $permission;
	}

	private function getNode( $getNamespace )
	{
	}
}
