<?php

namespace Foundation\Bootstrap;

use Foundation\Contracts\Foundation\Application;

class BootProviders
{
	/**
	 * Bootstrap the given application.
	 *
	 * @param  \Foundation\Contracts\Foundation\Application  $app
	 * @return void
	 */
	public function bootstrap(Application $app)
	{
		$app->boot();
	}
}
