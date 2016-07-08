<?php
namespace Penoaks\Bootstrap;

use Penoaks\Barebones\Bootstrap;
use Penoaks\Framework;

class RegisterProviders implements Bootstrap
{
	/**
	 * Bootstrap the given application.
	 *
	 * @param  \Penoaks\Framework  $fw
	 * @return void
	 */
	public function bootstrap(Framework $fw)
	{
		$fw->registerConfiguredProviders();
	}
}
