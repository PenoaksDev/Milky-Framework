<?php
namespace Penoaks\Bindings;

class ContextualBindingBuilder
{
	/**
	 * The underlying bindings instance.
	 *
	 * @var Bindings
	 */
	protected $bindings;

	/**
	 * The concrete instance.
	 *
	 * @var string
	 */
	protected $concrete;

	/**
	 * The abstract target.
	 *
	 * @var string
	 */
	protected $needs;

	/**
	 * Create a new contextual binding builder.
	 *
	 * @param  Bindings $bindings
	 * @param  string $concrete
	 * @return void
	 */
	public function __construct( Bindings $bindings, $concrete )

	{
		$this->concrete = $concrete;
		$this->bindings = $bindings;
	}

	/**
	 * Define the abstract target that depends on the context.
	 *
	 * @param  string $abstract
	 * @return $this
	 */
	public function needs( $abstract )

	{
		$this->needs = $abstract;

		return $this;
	}

	/**
	 * Define the implementation for the contextual binding.
	 *
	 * @param  \Closure|string $implementation
	 * @return void
	 */
	public function give( $implementation )

	{
		$this->bindings->addContextualBinding( $this->concrete, $this->needs, $implementation );
	}
}
