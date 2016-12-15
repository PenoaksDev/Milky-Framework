<?php namespace Milky\Account\Permissions;

use Milky\Exceptions\AccountException;
use Milky\Helpers\NS;

/**
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class PermissionModel
{
	private $localName;

	/**
	 * @var Permission
	 */
	private $node = null;

	private $description = '';

	private $enums = [];

	private $maxLen = -1;

	private $type;

	private $value = null;

	private $valueDefault = null;

	/**
	 * PermissionModel constructor.
	 *
	 * @param string|NS|Permission $node
	 */
	public function __construct( $node )
	{
		if ( is_string( $node ) )
			$node = new NS( $node );

		if ( !$node instanceof NS && !$node instanceof Permission)
			throw new AccountException( "Expected \$node to be either string, Permission, or NS" );

		$this->localName = $node->getLocalName();
		$this->node = $node instanceof Permission ? $node : $node->getPermission();
	}

	public function setNode( Permission $node )
	{
		$this->node = $node;
	}

	public function getNode()
	{
		return $this->node;
	}

	public function getDescription()
	{
		return $this->description;
	}

	public function getEnums()
	{
		return $this->enums;
	}

	public function getEnumsString()
	{
		return implode( '|', $this->enums );
	}

	public function getMaxLen()
	{
		return $this->maxLen;
	}

	public function getValue()
	{
		return $this->value;
	}

	public function getValueDefault()
	{
		return $this->valueDefault;
	}
}
