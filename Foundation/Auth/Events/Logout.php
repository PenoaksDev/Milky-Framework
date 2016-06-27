<?php

namespace Foundation\Auth\Events;

use Foundation\Queue\SerializesModels;

class Logout
{
	use SerializesModels;

	/**
	 * The authenticated user.
	 *
	 * @var \Foundation\Contracts\Auth\Authenticatable
	 */
	public $user;

	/**
	 * Create a new event instance.
	 *
	 * @param  \Foundation\Contracts\Auth\Authenticatable  $user
	 * @return void
	 */
	public function __construct($user)
	{
		$this->user = $user;
	}
}
