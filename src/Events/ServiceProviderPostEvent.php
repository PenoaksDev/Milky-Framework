<?php
namespace Penoaks\Events;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */

use Penoaks\Barebones\Event;
use Penoaks\Barebones\ServiceProvider;

class ServiceProviderPostEvent implements Event
{
	/**
	 * @var ServiceProvider
	 */
	private $provider;

	/**
	 * BootstrapPreEvent constructor.
	 *
	 * @param ServiceProvider $bootstrap
	 */
	public function __construct( $provider )
	{
		$this->provider = $provider;
	}

	/**
	 * Gets the bootstrap instance
	 *
	 * @return ServiceProvider
	 */
	public function getProvider()
	{
		return $this->provider;
	}

	/**
	 * Checks the $bootstrap class
	 *
	 * @param ServiceProvider $bootstrap
	 * @return bool
	 */
	public function is( ServiceProvider $provider )
	{
		return $this->provider == $provider || get_class( $this->provider ) == get_class( $provider );
	}
}
