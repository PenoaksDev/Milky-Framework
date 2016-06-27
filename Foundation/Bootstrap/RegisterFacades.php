<?php

namespace Foundation\Bootstrap;

use Foundation\Support\Facades\Facade;
use Foundation\AliasLoader;
use Foundation\Contracts\Foundation\Application;

class RegisterFacades
{
	/**
	 * Bootstrap the given application.
	 *
	 * @param  \Foundation\Contracts\Foundation\Application  $app
	 * @return void
	 */
	public function bootstrap(Application $app)
	{
		Facade::clearResolvedInstances();

		Facade::setFacadeApplication($app);

		AliasLoader::getInstance($app->make('config')->get('app.aliases'))->register();
	}
}
