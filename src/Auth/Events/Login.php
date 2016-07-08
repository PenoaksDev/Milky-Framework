<?php

namespace Penoaks\Auth\Events;

use Penoaks\Queue\SerializesModels;

class Login
{
	use SerializesModels;

	/**
	 * The authenticated user.
	 *
	 * @var \Penoaks\Contracts\Auth\Authenticatable
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
	 * @param  \Penoaks\Contracts\Auth\Authenticatable  $user
	 * @param  bool  $remember
	 * @return void
	 */
	public function __construct($user, $remember)
	{
		$this->user = $user;
		$this->remember = $remember;
	}
}
