<?php
namespace Penoaks\Cookie;

use Penoaks\Barebones\ServiceProvider;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class CookieServiceProvider extends ServiceProvider
{
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->bindings->singleton( 'cookie', function ( $bindings )
		{
			$config = $bindings['config']['session'];

			return ( new CookieJar )->setDefaultPathAndDomain( $config['path'], $config['domain'], $config['secure'] );
		} );
	}
}
