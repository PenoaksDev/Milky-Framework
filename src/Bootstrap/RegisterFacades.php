<?php

namespace Penoaks\Bootstrap;

use Penoaks\Framework\AliasLoader;
use Penoaks\Barebones\Bootstrap;
use Penoaks\Framework;
use Penoaks\Support\Facades\Facade;

class RegisterFacades implements Bootstrap
{
	/**
	 * Bootstrap the given application.
	 *
	 * @param  \Penoaks\Framework  $fw
	 * @return void
	 */
	public function bootstrap(Framework $fw)
	{
		Facade::clearResolvedInstances();

		Facade::setFacadeApplication($fw);

		AliasLoader::getInstance($fw->bindings->make('config')->get('app.aliases'))->register();
	}
}
