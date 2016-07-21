<?php
namespace Penoaks\Http\Middleware;

use Penoaks\Bindings\Bindings;
use Penoaks\Framework;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class BaseMiddleware
{
	/**
	 * @var Framework
	 */
	protected $fw;

	/**
	 * @var Bindings
	 */
	protected $bindings;

	public function __construct( Framework $fw, Bindings $bindings )
	{
		$this->fw = $fw;
		$this->bindings = $bindings;
	}
}
