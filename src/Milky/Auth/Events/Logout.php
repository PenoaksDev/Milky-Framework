<?php namespace Milky\Auth\Events;

use Illuminate\Queue\SerializesModels;

class Logout
{
	use SerializesModels;

	/**
	 * The authenticated user.
	 *
	 * @var Authenticatable
	 */
	public $user;

	/**
	 * Create a new event instance.
	 *
	 * @param  Authenticatable $user
	 * @return void
	 */
	public function __construct( $user )
	{
		$this->user = $user;
	}
}
