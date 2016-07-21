<?php namespace Milky\Auth\Events;

use Illuminate\Queue\SerializesModels;

class Login
{
	use SerializesModels;

	/**
	 * The authenticated user.
	 *
	 * @var Authenticatable
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
	 * @param  Authenticatable $user
	 * @param  bool $remember
	 * @return void
	 */
	public function __construct( $user, $remember )
	{
		$this->user = $user;
		$this->remember = $remember;
	}
}
