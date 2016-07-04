<?php

namespace Foundation\Bootstrap;

use Foundation\Framework;

class BootProviders
{
	/**
	 * Bootstrap the given application.
	 *
	 * @param  \Foundation\Framework  $fw
	 * @return void
	 */
	public function bootstrap(Framework $fw)
	{
		$fw->boot();
	}
}
