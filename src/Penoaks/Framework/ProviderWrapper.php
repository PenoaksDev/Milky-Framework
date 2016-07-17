<?php
namespace Penoaks\Framework;

use Penoaks\Barebones\ServiceProvider;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class ProviderWrapper
{
	/**
	 * @var ServiceProvider
	 */
	public $provider;

	/**
	 * @var bool
	 */
	public $loaded = false;

	public function __construct( $provider )
	{
		$this->provider = $provider;
	}
}
