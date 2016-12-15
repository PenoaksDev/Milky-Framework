<?php namespace Milky\Account\Permissions;

/**
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class PermissionDefault
{
	private static $defaults = [];

	public static function ADMIN()
	{
		if ( !array_key_exists( 'admin', static::$defaults ) )
			static::$defaults['admin'] = new static( 'admin', 'sys.admin' );
		return static::$defaults['admin'];
	}

	public static function BANNED()
	{
		if ( !array_key_exists( 'banned', static::$defaults ) )
			static::$defaults['banned'] = new static( 'banned', 'sys.banned' );
		return static::$defaults['banned'];
	}

	public static function DEF()
	{
		if ( !array_key_exists( 'def', static::$defaults ) )
			static::$defaults['def'] = new static( 'def', 'default' );
		return static::$defaults['def'];
	}

	public static function EVERYBODY()
	{
		if ( !array_key_exists( 'everybody', static::$defaults ) )
			static::$defaults['everybody'] = new static( 'everybody', '' );
		return static::$defaults['everybody'];
	}

	public static function OP()
	{
		if ( !array_key_exists( 'op', static::$defaults ) )
			static::$defaults['op'] = new static( 'op', 'sys.op' );
		return static::$defaults['op'];
	}

	public static function WHITELISTED()
	{
		if ( !array_key_exists( 'whitelisted', static::$defaults ) )
			static::$defaults['whitelisted'] = new static( 'whitelisted', 'sys.whitelisted' );
		return static::$defaults['whitelisted'];
	}

	public static function isDefault( Permission $permission )
	{
		foreach ( static::$defaults as $default )
			if ( $default->getNamespace() == $permission->getNamespace() )
				return true;
		return false;
	}

	/**
	 * PermissionDefault namespace
	 *
	 * @var string
	 */
	private $namespace;

	/**
	 * The permission key name
	 *
	 * @var string
	 */
	private $key;

	/**
	 * PermissionDefault constructor.
	 *
	 * @param $namespace
	 */
	protected function __construct( $key, $namespace )
	{
		$this->key = $key;
		$this->namespace = $namespace;
	}

	/**
	 * Get permission local name, e.g., some.permission.node = node
	 *
	 * @return string
	 */
	public function getLocalName()
	{
		return strpos( $this->namespace, '.' ) !== false ? substr( $this->namespace, strpos( $this->namespace, '.' ) + 1 ) : $this->namespace;
	}

	/**
	 * Get the Permission Default namespace
	 *
	 * @return string
	 */
	public function getNamespace()
	{
		return $this->namespace;
	}

	public function getNode()
	{
		$permission = PermissionManager::i()->getNode( $this->namespace );

		if ( !$permission )
		{
			if ( $this->key == 'everybody' )
			{
				$permission = PermissionManager::i()->createNode( $this->namespace, PermissionType::BOOL );
				$permission->getModel()->setValue( true );
				$permission->getModel()->setValueDefault( true );
			}
			else
				$permission = PermissionManager::i()->createNode( $this->getNamespace() );

			switch ( $this->key )
			{
				case 'def':
					$permission->getModel()->setDescription( "Used as the default permission node if one does not exist. (DO NOT EDIT!)" );
					break;
				case 'enerybody':
					$permission->getModel()->setDescription( "This node is used for the 'everybody' permission. (DO NOT EDIT!)" );
					break;
				case 'op':
					$permission->getModel()->setDescription( "Indicates OP entities. (DO NOT EDIT!)" );
					break;
				case 'admin':
					$permission->getModel()->setDescription( "Indicates ADMIN entities. (DO NOT EDIT!)" );
					break;
				case 'banned':
					$permission->getModel()->setDescription( "Indicates BANNED entities. (DO NOT EDIT!)" );
					break;
				case 'whitelisted':
					$permission->getModel()->setDescription( "Indicates WHITELISTED entities. (DO NOT EDIT!)" );
					break;
			}

			$permission->commit();
		}

		return $permission;
	}

	public function __toString()
	{
		return "PermissionDefault{namespace={$this->getNamespace()}}";
	}
}
