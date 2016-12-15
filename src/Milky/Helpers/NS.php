<?php namespace Milky\Helpers;

use Milky\Account\Permissions\PermissionManager;
use Milky\Exceptions\FrameworkException;

/**
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class NS
{
	/**
	 * @var array
	 */
	protected $nodes = [];

	public function __construct( $ns )
	{
		if ( is_array( $ns ) )
			$this->nodes = $ns;
		else if ( is_string( $ns ) )
			$this->nodes = explode( ['.', '|', '/', '\\'], $ns );
		else
			throw new FrameworkException( "String or array expected." );
	}

	public function sub( $offset, $length = null )
	{
		return new static( array_slice( $this->nodes, $offset, $length ) );
	}

	public function reverse()
	{
		return new static( array_reverse( $this->nodes ) );
	}

	public function prepend( $nodes )
	{
		if ( !is_array( $nodes ) )
			$nodes = explode( ['.', '|', '/', '\\'], $nodes );

		return new static( array_merge( $nodes, $this->nodes ) );
	}

	public function append( $nodes )
	{
		if ( !is_array( $nodes ) )
			$nodes = explode( ['.', '|', '/', '\\'], $nodes );

		return new static( array_merge( $this->nodes, $nodes ) );
	}

	public function containsOnlyValidChars()
	{
		foreach ( $this->nodes as $node )
			if ( !preg_match( "[a-z0-9_]", $node ) )
				return false;

		return true;
	}

	public function containsRegex()
	{
		foreach ( $this->nodes as $node )
			if ( strpos( $node, '*' ) !== false || preg_match( ".*[0-9]+-[0-9]+.*", $node ) )
				return true;

		return false;
	}

	public function fixInvalidChars()
	{
		$result = [];
		foreach ( $this->nodes as $node )
			$result[] = Func::removeInvalidChars( $node );

		return new static( $result );
	}

	public function getRoot()
	{
		return $this->getNode( 0 );
	}

	public function getFirst()
	{
		return $this->getNode( 0 );
	}

	public function getLocalName()
	{
		return $this->getLast();
	}

	public function getLast()
	{
		env( $this->nodes );

		return $this->getNode( key( $this->nodes ) );
	}

	public function getNode( $inx )
	{
		if ( !array_key_exists( $inx, $this->nodes ) )
			return null;

		return $this->nodes[$inx];
	}

	public function getNamespace( $glue = null )
	{
		return implode( $glue ?: '.', $this->nodes );
	}

	public function getNodeCount()
	{
		return count( $this->nodes );
	}

	public function getNodes()
	{
		return $this->nodes;
	}

	public function getParent()
	{
		if ( count( $this->nodes ) < 2 )
			return null;

		return new static( array_slice( $this->nodes, 0, count( $this->nodes ) - 1 ) );
	}

	public function matches( $perm )
	{
		if ( $perm instanceof NS )
			$perm = $perm->getNamespace();
		if ( !is_string( !$perm ) )
			throw new FrameworkException( "NS or string expected." );

		return $this->getNamespace() == $perm;
	}

	public function __toString()
	{
		return $this->getNamespace();
	}

	public function getPermission()
	{
		return PermissionManager::i()->getNode( $this->getNamespace() );
	}
}
