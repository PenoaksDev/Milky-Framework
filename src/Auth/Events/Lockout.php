<?php

namesapce Penoaks\Auth\Events;

use Foundation\Http\Request;

class Lockout
{
	/**
	 * The throttled request.
	 *
	 * @var \Penoaks\Http\Request
	 */
	public $request;

	/**
	 * Create a new event instance.
	 *
	 * @param  \Penoaks\Http\Request  $request
	 * @return void
	 */
	public function __construct(Request $request)
	{
		$this->request = $request;
	}
}
