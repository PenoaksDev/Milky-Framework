<?php namespace Milky\Account\Permissions;

use Milky\Exceptions\Auth\PermissionException;

/**
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class Permission
{
	const EVERYBODY = "sys.everybody";
	const OP = "sys.op";
	const ADMIN = "sys.admin";
	const BANNED = "sys.banned";
	const WHITELISTED = "sys.whitelisted";

	/**
	 * @var PermissionManager
	 */
	private $mgr;

	/**
	 * @var string
	 */
	private $localName;

	/**
	 * @var Callable[]
	 */
	private $policyMethods = [];

	public function __construct( $mgr, $localName )
	{
		if ( !preg_match( "/[a-z0-9_]*/", $localName ) )
			throw new PermissionException( "The permission name [$localName] can only contain the characters a-z, 0-9, and _." );

		$this->mgr = $mgr;
		$this->localName = $localName;
	}

	public function getLocalName()
	{
		return strtolower( $this->localName );
	}

	public function __toString()
	{
		return "Permission(name={$this->localName})";
	}

	public function addPolicyMethod( Callable $callable )
	{
		$this->policyMethods[] = $callable;
	}

	public function getPolicyMethods()
	{
		return $this->policyMethods;
	}
}
