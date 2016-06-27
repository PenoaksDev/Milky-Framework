<?php

namespace Foundation\Auth\Events;

class Failed
{
	/**
	 * The user the attempter was trying to authenticate as.
	 *
	 * @var \Foundation\Contracts\Auth\Authenticatable|null
	 */
	public $user;

	/**
	 * The credentials provided by the attempter.
	 *
	 * @var array
	 */
	public $credentials;

	/**
	 * Create a new event instance.
	 *
	 * @param  \Foundation\Contracts\Auth\Authenticatable|null  $user
	 * @param  array  $credentials
	 */
	public function __construct($user, $credentials)
	{
		$this->user = $user;
		$this->credentials = $credentials;
	}
}
