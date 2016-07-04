<?php

namespace Foundation\Auth\Events;

use Foundation\Http\Request;

class Lockout
{
	/**
	 * The throttled request.
	 *
	 * @var \Foundation\Http\Request
	 */
	public $request;

	/**
	 * Create a new event instance.
	 *
	 * @param  \Foundation\Http\Request  $request
	 * @return void
	 */
	public function __construct(Request $request)
	{
		$this->request = $request;
	}
}
