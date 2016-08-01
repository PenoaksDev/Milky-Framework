<?php namespace Milky\Account\Types;

use Milky\Database\Eloquent\Builder;
use Milky\Database\Eloquent\Model;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class EloquentAccount implements Account
{
	/**
	 * @var Model
	 */
	private $model;

	/**
	 * @var array
	 */
	private static $config = [];

	/**
	 * @param array $config
	 */
	public static function setConfig( array $config )
	{
		static::$config = $config;
	}

	/**
	 * EloquentAccount constructor.
	 *
	 * @param Model|Builder $model
	 */
	public function __construct( Model $model )
	{
		$this->model = $model;
	}

	/**
	 * Compiles a human readable display name, e.g., John Smith
	 *
	 * @return string A human readable display name
	 */
	function getDisplayName()
	{
		return $this->model->name;
	}

	/**
	 * Returns the AcctId for this Account
	 *
	 * @return string Account Id
	 */
	function getId()
	{
		return $this->model->id;
	}

	/**
	 * Get the password for the user.
	 *
	 * @return string
	 */
	function getAuthPassword()
	{
		return $this->model->password;
	}

	/**
	 * @return string
	 */
	function getRememberToken()
	{
		return $this->model->remember_token;
	}

	function setRememberToken( $token )
	{
		$this->model->remember_token = $token;
	}
}
