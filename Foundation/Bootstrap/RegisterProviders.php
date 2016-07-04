<?php

namespace Foundation\Bootstrap;

use Foundation\Framework;

class RegisterProviders
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
