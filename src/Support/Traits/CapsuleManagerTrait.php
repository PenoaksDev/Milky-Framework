<?php

namesapce Penoaks\Support\Traits;

use Foundation\Support\Fluent;
use Foundation\Framework;

trait CapsuleManagerTrait
{
	/**
	 * The current globally used instance.
	 *
	 * @var object
	 */
	protected static $instance;

	/**
	 * The bindings instance.
	 *
	 * @var \Penoaks\Framework
	 */
	protected $bindings;

	/**
	 * Setup the IoC bindings instance.
	 *
	 * @param  \Penoaks\Framework  $bindings
	 * @return void
	 */
	protected function setupBindings(Bindings $bindings)
	{
		$this->bindings = $bindings;

		if (! $this->bindings->bound('config'))
{
			$this->bindings->instance('config', new Fluent);
		}
	}

	/**
	 * Make this capsule instance available globally.
	 *
	 * @return void
	 */
	public function setAsGlobal()
	{
		static::$instance = $this;
	}

	/**
	 * Get the IoC bindings instance.
	 *
	 * @return \Penoaks\Framework
	 */
	public function getBindings()
	{
		return $this->bindings;
	}

	/**
	 * Set the IoC bindings instance.
	 *
	 * @param  \Penoaks\Framework  $bindings
	 * @return void
	 */
	public function setBindings(Bindings $bindings)
	{
		$this->bindings = $bindings;
	}
}
