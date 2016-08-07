<?php namespace Milky\Account\Permissions;

use Milky\Exceptions\AccountException;
use Milky\Helpers\NS;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class Permission
{
	/**
	 * @var Permission[]
	 */
	private $children = [];

	/**
	 * @var PermissionModel
	 */
	private $model;

	/**
	 * @var string
	 */
	private $localName;

	/**
	 * @var Permission
	 */
	private $parent;

	public function __construct( $localName, $parent = null, $type = null )
	{
		if ( !preg_match( "[a-z0-9_]*", $localName ) )
			throw new AccountException( "The permission local name [$localName] can only contain the characters a-z, 0-9, and _." );

		$this->localName = $localName;
		$this->parent = $parent;

		$this->model = new PermissionModel( $localName, $type, $this );
		PermissionManager::i()->addPermission( $this );
	}

	public function addChild( Permission $node )
	{
		$this->children[$node->getLocalName()] = $node;
	}

	public function getChild( $name )
	{
		if ( !array_key_exists( strtolower( $name ), $this->children ) )
			return null;

		return $this->children[strtolower( $name )];
	}

	public function getChildren()
	{
		return $this->children;
	}

	public function getChildrenRecursive( $includeParents = false, &$array = [] )
	{
		if ( $includeParents || !$this->hasChildren() )
			$array[] = $this;

		foreach ( $this->children as $child )
			$child->getChildrenRecursive( $includeParents, $array );

		return $array;
	}

	public function commit()
	{
		// Save changes
	}

	/**
	 * @return NS
	 */
	public function getNamespace()
	{
		$ns = [];
		$ladder = $this;

		do
		{
			$ns[] = $ladder->getLocalName();
			$ladder = $ladder->getParent();
		}
		while ( !is_null( $ladder ) );

		return new NS( array_reverse( $ns ) );
	}

	public function compare( Permission $perm )
	{
		if ( $this->getNamespace() == $perm->getNamespace() )
			return 0;

		$ns1 = $this->getNamespace();
		$ns2 = $perm->getNamespace();

		for ( $i = 0; $i < min( $ns1->getNodeCount(), $ns2->getNodeCount() ); $i++ )
			if ( $ns1->getNode( $i ) != $ns2->getNode( $i ) )
				return strcmp( $ns1->getNode( $i ), $ns2->getNode( $i ) );

		return $ns1->getNodeCount() > $ns2->getNodeCount() ? -1 : 1;
	}

	public function getLocalName()
	{
		return strtolower( $this->localName );
	}

	public function getModel()
	{
		return $this->model;
	}

	public function getParent()
	{
		return $this->parent;
	}

	public function getType()
	{
		return $this->model->getType();
	}

	public function hasChildren()
	{
		return count( $this->children ) > 0;
	}

	public function hasParent()
	{
		return !is_null( $this->parent );
	}

	public function __toString()
	{
		return "Permission(name={$this->localName},parent={$this->parent},model={$this->model})";
	}
}
