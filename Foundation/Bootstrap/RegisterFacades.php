<?php

namespace Foundation\Bootstrap;

use Foundation\AliasLoader;
use Foundation\Framework;
use Foundation\Interfaces\Bootstrap;
use Foundation\Support\Facades\Facade;

class RegisterFacades implements Bootstrap
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
