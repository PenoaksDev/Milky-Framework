<?php
namespace Foundation\Bootstrap;

use Foundation\Framework;
use Foundation\Interfaces\Bootstrap;

class RegisterProviders implements Bootstrap
{
	/**
	 * Bootstrap the given application.
	 *
	 * @param  \Foundation\Framework  $fw
	 * @return void
	 */
	public function bootstrap(Framework $fw)
	{
		$fw->registerConfiguredProviders();
	}
}
