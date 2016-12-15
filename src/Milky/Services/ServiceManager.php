<?php namespace Milky\Services;

use Milky\Framework;

/**
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class ServiceManager
{
	/**
	 * @var ServiceProvider[][]
	 */
	private static $serviceProviders = [];

	/**
	 * @param string $service
	 * @param int $priority
	 *
	 * @return ServiceProvider
	 */
	public static function getServiceProvider( $service, $priority = 0 )
	{
		foreach ( array_keys( static::$serviceProviders ) as $key )
			if ( is_subclass_of( $service, $key, true ) )
			{
				foreach ( static::$serviceProviders[$key] as $provider )
					if ( $provider->getPriority() == $priority )
						return $provider;
				if ( count( static::$serviceProviders[$key] ) > 0 )
					return static::$serviceProviders[$key][0];
			}

		return null;
	}

	/**
	 * @param string $service
	 * @param ServiceProvider $provider
	 * @param int $priority
	 */
	public static function registerServiceProvider( $service, ServiceProvider $provider )
	{
		if ( !array_key_exists( $service, static::$serviceProviders ) )
			static::$serviceProviders[$service] = [];

		static::$serviceProviders[$service] = array_merge( static::$serviceProviders[$service], [$provider] );

		Framework::hooks()->trigger( 'service.registered', compact( 'server', 'provider' ) );
	}

	public static function getKnownServices()
	{
		return array_keys( static::$serviceProviders );
	}
}
