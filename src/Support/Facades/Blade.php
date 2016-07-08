<?php

namesapce Penoaks\Support\Facades;

/**
 * @see \Penoaks\View\Compilers\BladeCompiler
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
		return static::$fw->bindings['view']->getEngineResolver()->resolve('blade')->getCompiler();
	}
}
