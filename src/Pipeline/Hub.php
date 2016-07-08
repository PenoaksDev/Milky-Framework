<?php

namespace Penoaks\Pipeline;

use Closure;
use Penoaks\Framework;
use Penoaks\Contracts\Pipeline\Hub as HubContract;

class Hub implements HubContract
{
	/**
	 * The bindings implementation.
	 *
	 * @var \Penoaks\Framework
	 */
	protected $bindings;

	/**
	 * All of the available pipelines.
	 *
	 * @var array
	 */
	protected $pipelines = [];

	/**
	 * Create a new Hub instance.
	 *
	 * @param  \Penoaks\Framework  $bindings
	 * @return void
	 */
	public function __construct(Bindings $bindings)
	{
		$this->bindings = $bindings;
	}

	/**
	 * Define the default named pipeline.
	 *
	 * @param  \Closure  $callback
	 * @return void
	 */
	public function defaults(Closure $callback)
	{
		return $this->pipeline('default', $callback);
	}

	/**
	 * Define a new named pipeline.
	 *
	 * @param  string  $name
	 * @param  \Closure  $callback
	 * @return void
	 */
	public function pipeline($name, Closure $callback)
	{
		$this->pipelines[$name] = $callback;
	}

	/**
	 * Send an object through one of the available pipelines.
	 *
	 * @param  mixed  $object
	 * @param  string|null  $pipeline
	 * @return mixed
	 */
	public function pipe($object, $pipeline = null)
	{
		$pipeline = $pipeline ?: 'default';

		return call_user_func(
			$this->pipelines[$pipeline], new Pipeline($this->bindings), $object
		);
	}
}
