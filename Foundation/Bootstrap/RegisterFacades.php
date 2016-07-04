<?php

namespace Foundation\Bootstrap;

use Foundation\Support\Facades\Facade;
use Foundation\AliasLoader;
use Foundation\Framework;

class RegisterFacades
{
	/**
	 * Bootstrap the given application.
	 *
	 * @param  \Foundation\Framework  $fw
	 * @return void
	 */
	public function bootstrap(Framework $fw)
	{
		Facade::clearResolvedInstances();

		Facade::setFacadeApplication($fw);

		AliasLoader::getInstance($fw->bindings->make('config')->get('app.aliases'))->register();
	}
}
