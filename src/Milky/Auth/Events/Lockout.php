<?php namespace Milky\Auth\Events;

use Milky\Http\Request;

class Lockout
{
	/**
	 * The throttled request.
	 *
	 * @var Request
	 */
	public $request;

	/**
	 * Create a new event instance.
	 *
	 * @param  Request $request
	 * @return void
	 */
	public function __construct( Request $request )
	{
		$this->request = $request;
	}
}
