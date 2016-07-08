<?php

namesapce Penoaks\Testing\Concerns;

trait InteractsWithBindings
{
	/**
	 * Register an instance of an object in the bindings.
	 *
	 * @param  string  $abstract
	 * @param  object  $instance
	 * @return object
	 */
	protected function instance($abstract, $instance)
	{
		$this->fw->bindings->instance($abstract, $instance);

		return $instance;
	}
}
