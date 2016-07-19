<?php
namespace Penoaks\Providers;

use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;
use Penoaks\Framework;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
abstract class ServiceProvider extends IlluminateServiceProvider
{
	/**
	 * ServiceProvider constructor.
	 *
	 * @param Framework $fw
	 */
	public function __construct( $fw )
	{
		/** @noinspection PhpParamsInspection */
		parent::__construct( $fw ); // FORCE IT!!!
	}
}
