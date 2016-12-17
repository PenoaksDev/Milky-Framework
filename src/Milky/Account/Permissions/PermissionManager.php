<?php namespace Milky\Account\Permissions;

use HolyWorlds\Support\Util;
use Milky\Account\Models\PermissibleEntity;
use Milky\Account\Models\PermissionDefaults;
use Milky\Binding\UniversalBuilder;
use Milky\Exceptions\Auth\PermissionException;
use Milky\Facades\Acct;
use Milky\Facades\Config;
use Milky\Facades\Log;
use Milky\Helpers\Arr;

/**
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class PermissionManager
{
	/**
	 * @var Policy[]
	 */
	protected $loadedPolicies = [];

	/**
	 * @var Permission[]
	 */
	protected $cachedPermissions;

	/**
	 * @return PermissionManager
	 */
	public static function i()
	{
		return UniversalBuilder::resolveClass( static::class );
	}

	/**
	 * @param $name
	 *
	 * @return string
	 */
	public static function getAlias( $name )
	{
		if ( $perm = Config::get( 'permissions.alias.' . $name ) )
			return $perm;

		return $name;
	}

	/**
	 * @param $namespace
	 *
	 * @return Permission
	 */
	public function getPermission( $namespace )
	{
		$node = Arr::get( $this->cachedPermissions, $namespace . '.__node' );
		if ( !$node )
		{
			$node = new Permission( $this, basename( str_replace( ".", "/", $namespace ) ) );
			Arr::set( $this->cachedPermissions, $namespace . '.__node', $node );
		}

		return $node;
	}

	/**
	 * Adds a new policy checker.
	 *
	 * Policies are checked for permissions before they are checked by the general permission backend
	 *
	 * @param Policy $policy
	 */
	public function policy( Policy $policy )
	{
		$this->loadedPolicies[] = $policy;

		// Caches the policy nodes as a nestable tree
		foreach ( $policy->getNodes() as $namespace => $callable )
			$this->getPermission( $namespace )->addPolicyMethod( $callable );
	}

	protected function checkWalker( $namespace, $assigned_permissions )
	{
		// Check Permission Root -- Recursive
		$result_root = str_contains( $namespace, "." ) ? $this->checkWalker( substr( $namespace, 0, strrpos( $namespace, '.' ) ), $assigned_permissions ) : PermissionValues::UNSET;
		$result_permission = PermissionValues::UNSET;
		$result_policy = PermissionValues::UNSET;

		if ( $permission = $this->getPermission( $namespace ) )
		{
			// Check Permission Assignment
			foreach ( $assigned_permissions as $p )
			{
				if ( $p->permission == null || strlen( $p->permission ) == 0 )
					continue;

				try
				{
					if ( preg_match( static::prepareExpression( $p->permission ), $namespace ) )
					{
						$result_permission = $p->value;

						if ( !PermissionValues::valid( $result_permission ) || $result_permission == PermissionValues::UNSET )
							$result_permission = PermissionValues::YES;
						if ( $result_permission == PermissionValues::ALWAYS || $result_permission == PermissionValues::NEVER )
							break;
					}
				}
				catch ( \Exception $e )
				{
					// Ignore preg_match() exceptions
				}
			}

			// Check Policies
			foreach ( $permission->getPolicyMethods() as $callable )
			{
				$result = UniversalBuilder::call( $callable, compact( 'entity' ) );
				if ( PermissionValues::valid( $result ) )
				{
					if ( $result_policy == PermissionValues::ALWAYS || $result_policy == PermissionValues::NEVER )
						break;
					$result_policy = $result;
				}
			}
		}

		$result = $result_root;

		// Check if valid result
		if ( !PermissionValues::valid( $result ) )
			$result = PermissionValues::UNSET;

		// Check if permission assignment trumps root node
		if ( $result_permission != PermissionValues::UNSET && PermissionValues::valid( $result_permission ) )
		{
			if ( $result == PermissionValues::YES || $result == PermissionValues::NO || $result == PermissionValues::UNSET )
				$result = $result_permission;
			else if ( $result_permission == PermissionValues::ALWAYS || $result_permission == PermissionValues::NEVER )
				$result = $result_permission;
		}

		// If the result is still unset, we check for a recommended unset default.
		if ( $result == PermissionValues::UNSET )
		{
			$node_default = PermissionDefaults::find( $namespace, true );
			if ( $node_default != null )
				$result = $node_default->value_default;
		}

		// Check if policy trumps permission assignment and/or root node
		if ( $result_policy != PermissionValues::UNSET && PermissionValues::valid( $result_policy ) )
		{
			if ( $result == PermissionValues::YES || $result == PermissionValues::NO || $result == PermissionValues::UNSET )
				$result = $result_policy;
			else if ( $result_policy == PermissionValues::ALWAYS || $result_policy == PermissionValues::NEVER )
				$result = $result_policy;
		}

		// Log::debug( $namespace . " == " . $result );

		return $result;
	}

	/**
	 * Checks a raw singular namespace and returns the unhindered result
	 *
	 * @param $namespace
	 * @param PermissibleEntity|null $entity
	 *
	 * @return string
	 */
	protected function checkRaw( $namespace, PermissibleEntity $entity = null )
	{
		if ( $namespace == null || strlen( $namespace ) == 0 || $namespace == "everybody" || $namespace == "-1" )
			$namespace = Permission::EVERYBODY;
		if ( $namespace == "op" || $namespace == "0" )
			$namespace = Permission::OP;
		if ( $namespace == "admin" )
			$namespace = Permission::ADMIN;
		if ( $namespace == "banned" )
			$namespace = Permission::BANNED;
		if ( $namespace == "whitelisted" )
			$namespace = Permission::WHITELISTED;

		$namespace = static::getAlias( $namespace );

		if ( !preg_match( "/[a-z0-9_.]*/", $namespace ) )
			throw new PermissionException( "The permission namespace [$namespace] can only contain the characters 'a-z0-9_.'." );

		$permission_defaults = PermissionDefaults::find( $namespace );

		if ( $entity === null )
		{
			if ( !Acct::check() )
			{
				$result = $this->checkWalker( $namespace, [] );
				if ( $result == PermissionValues::UNSET )
					$result = $permission_defaults->value_default;

				return $result;
			}
			$entity = Acct::acct();
		}

		$group_state = PermissionValues::UNSET;

		/*
		 * Check group permissible state
		 */
		foreach ( $entity->groups() as $group )
		{
			$group_state = $this->checkRaw( $namespace, $group );
			if ( $group_state == PermissionValues::ALWAYS || $group_state == PermissionValues::NEVER )
				break;
		}

		/*
		 * Check user permissible state
		 */
		$user_state = $this->checkWalker( $namespace, $entity->permissions() );

		if ( $group_state == PermissionValues::ALWAYS || $group_state == PermissionValues::NEVER )
		{
			if ( $user_state == PermissionValues::ALWAYS )
				return true;
			else if ( $user_state == PermissionValues::NEVER )
				return false;
			else
				return $group_state;
		}
		else if ( $group_state == PermissionValues::UNSET && $user_state == PermissionValues::UNSET )
			return $permission_defaults->value_default;
		else if ( $user_state == PermissionValues::UNSET )
			return $group_state;
		else // if ( $group_state == PermissionValues::UNSET )
			return $user_state;
	}

	/**
	 * Checks a permission for assignment and policy checks
	 *
	 * @param string|array $namespace
	 * @param PermissibleEntity|null $entity
	 *
	 * @return bool
	 */
	public function check( $namespaces, PermissibleModel $model = null, PermissibleEntity $entity = null )
	{
		// Allows for static calling
		$instance = $this ?: self::i();

		if ( !is_array( $namespaces ) )
			$namespaces = [$namespaces];

		foreach ( $namespaces as &$ns )
		{
			if ( !is_string( $ns ) )
				throw new PermissionException( "The permission namespace must be a string." );

			$ns = static::getAlias( $ns );

			if ( !preg_match( "/[a-z0-9_.]*/", $ns ) )
				throw new PermissionException( "The permission namespace [$ns] can only contain the characters 'a-z0-9_.'." );
		}

		if ( $model != null )
		{
			if ( $model instanceof PermissibleModel )
			{
				$namespaces_new = $namespaces;
				foreach ( $model->getIdentifiers() as $key => $id )
					$namespaces_new = str_replace( "{" . $key . "}", $id, $namespaces_new );

				// Removes untouched values. Seems like a good idea, except in the cast that the user wishes to check a generic permission node.
				// $namespaces_new = array_diff( $namespaces_new, array_intersect( $namespaces_new, $namespaces ) );

				// Log::debug( "Amending namespaces [" . str_replace( "\n", "", var_export( $namespaces, true ) ) . "] with model [" . get_class( $model ) . "] resulting in [" . str_replace( "\n", "", var_export( $namespaces_new, true ) ) . "]" );

				return $instance->check( $namespaces_new, null, $entity );
			}
			else
				throw new PermissionException( "The 'model' must implement PermissibleModel." );
		}

		$current_state = PermissionValues::UNSET;

		array_walk( $namespaces, function ( $ns ) use ( &$instance, &$entity, &$current_state )
		{
			$current_state = $instance->checkRaw( $ns, $entity );

			Log::debug( "Checking permission [" . $ns . "] on entity [" . ( $entity ? get_class( $entity ) : "null" ) . "] with result [" . $current_state . "]" );

			if ( $current_state == PermissionValues::ALWAYS )
				return true;
			if ( $current_state == PermissionValues::NEVER )
				return false;
			if ( $current_state == PermissionValues::UNSET || !PermissionValues::valid( $current_state ) )
				throw new PermissionException( "Permission Exception!" );
		} );

		return $current_state == PermissionValues::YES;
	}

	public static function prepareExpression( $perm )
	{
		if ( Util::startsWith( $perm, '$' ) )
			return substr( $perm, 1 );

		$perm = str_replace( '.', '\.', $perm );
		$perm = str_replace( '*', '(.*)', $perm );

		if ( preg_match( '/(\d+)-(\d+)/', $perm, $matches, PREG_OFFSET_CAPTURE ) )
			foreach ( $matches as $match )
				$perm = str_replace( $match[0], '(' . implode( '|', range( $match[1], $match[2] ) ) . ')', $perm );

		return '/' . $perm . '/';
	}
}
