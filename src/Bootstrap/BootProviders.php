<?php
namespace Penoaks\Bootstrap;

use Penoaks\Framework;
use Penoaks\Interfaces\Bootstrap;

class BootProviders implements Bootstrap
{
	/**
	 * Bootstrap the given application.
	 *
	 * @param  \Penoaks\Framework  $fw
	 * @return void
	 */
	public function bootstrap(Framework $fw)
	{
		$fw->boot();
	}
}
