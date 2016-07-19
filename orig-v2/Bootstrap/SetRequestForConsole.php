<?php
namespace Penoaks\Bootstrap;

use Penoaks\Barebones\Bootstrap;
use Penoaks\Bindings\Bindings;
use Penoaks\Framework;
use Penoaks\Http\Request;

class SetRequestForConsole implements Bootstrap
{
	/**
	 * Bootstrap the given application.
	 *
	 * @param  \Penoaks\Framework $fw
	 * @return void
	 */
	public function boot( Bindings $bindings )
	{
		$url = $bindings->make( 'config' )->get( 'app.url', 'http://localhost' );

		$bindings->instance( 'request', Request::create( $url, 'GET', [], [], [], $_SERVER ) );
	}
}
