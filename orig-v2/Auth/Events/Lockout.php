<?php

namespace Penoaks\Auth\Events;

use Penoaks\Http\Request;

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
