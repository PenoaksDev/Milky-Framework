<?php

namespace Penoaks\Auth\Events;

use Penoaks\Queue\SerializesModels;

class Logout
{
	use SerializesModels;

	/**
	 * The authenticated user.
	 *
	 * @var \Penoaks\Contracts\Auth\Authenticatable
	 */
	public $user;

	/**
	 * Create a new event instance.
	 *
	 * @param  \Penoaks\Contracts\Auth\Authenticatable  $user
	 * @return void
	 */
	public function __construct($user)
	{
		$this->user = $user;
	}
}
