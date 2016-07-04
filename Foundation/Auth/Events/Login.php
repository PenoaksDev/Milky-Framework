<?php

namespace Foundation\Auth\Events;

use Foundation\Queue\SerializesModels;

class Login
{
	use SerializesModels;

	/**
	 * The authenticated user.
	 *
	 * @var \Foundation\Contracts\Auth\Authenticatable
	 */
	public $user;

	/**
	 * Indicates if the user should be "remembered".
	 *
	 * @var bool
	 */
	public $remember;

	/**
	 * Create a new event instance.
	 *
	 * @param  \Foundation\Contracts\Auth\Authenticatable  $user
	 * @param  bool  $remember
	 * @return void
	 */
	public function __construct($user, $remember)
	{
		$this->user = $user;
		$this->remember = $remember;
	}
}
