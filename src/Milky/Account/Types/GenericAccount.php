<?php namespace Milky\Account\Types;

/**
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class GenericAccount implements Account
{
	/**
	 * Dynamic properties
	 *
	 * @var array
	 */
	protected $properties = [];

	/**
	 * GenericAccount constructor.
	 *
	 * @param array $properties
	 */
	public function __construct( $properties = [] )
	{
		$this->properties = $properties;
	}

	/**
	 * Compiles a human readable display name, e.g., John Smith
	 *
	 * @return string A human readable display name
	 */
	public function getDisplayName()
	{
		return $this->name;
	}

	/**
	 * Returns the AcctId for this Account
	 *
	 * @return string Account Id
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Get the password for the user.
	 *
	 * @return string
	 */
	public function getAuthPassword()
	{
		return $this->password;
	}

	/**
	 * @return string
	 */
	public function getRememberToken()
	{
		return $this->remember_token;
	}

	public function setRememberToken( $token )
	{
		$this->remember_token = $token;
	}

	public function __get( $key )
	{
		return $this->properties[$key];
	}

	public function __set( $key, $value )
	{
		$this->properties[$key] = $value;
	}

	/**
	 * Dynamically check if a value is set on the user.
	 *
	 * @param  string $key
	 * @return bool
	 */
	public function __isset( $key )
	{
		return isset( $this->properties[$key] );
	}

	/**
	 * Dynamically unset a value on the user.
	 *
	 * @param  string $key
	 * @return void
	 */
	public function __unset( $key )
	{
		unset( $this->properties[$key] );
	}

	public function offsetExists( $offset )
	{
		return array_key_exists( $this->properties, $offset );
	}

	public function offsetGet( $offset )
	{
		return $this->properties[$offset];
	}

	public function offsetSet( $offset, $value )
	{
		$this->properties[$offset] = $value;
	}

	public function offsetUnset( $offset )
	{
		unset( $this->properties[$offset] );
	}

	public function save()
	{
		// TODO Implement??
	}

	public function isActivated()
	{
		return true;
	}
}
