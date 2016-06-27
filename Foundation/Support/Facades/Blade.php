<?php

namespace Foundation\Support\Facades;

/**
 * @see \Foundation\View\Compilers\BladeCompiler
 */
class Blade extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return static::$app['view']->getEngineResolver()->resolve('blade')->getCompiler();
	}
}
