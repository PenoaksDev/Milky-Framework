<?php namespace Milky\Http\View\Engines;

use Closure;
use InvalidArgumentException;
use Milky\Binding\UniversalBuilder;

class EngineResolver
{
	/**
	 * The array of engine resolvers.
	 *
	 * @var array
	 */
	protected $resolvers = [];

	/**
	 * The resolved engine instances.
	 *
	 * @var array
	 */
	protected $resolved = [];

	/**
	 * @return EngineResolver
	 */
	public static function i()
	{
		return UniversalBuilder::resolveClass( static::class );
	}

	/**
	 * Register a new engine resolver.
	 *
	 * The engine string typically corresponds to a file extension.
	 *
	 * @param  string   $engine
	 * @param  \Closure  $resolver
	 * @return void
	 */
	public function register($engine, Closure $resolver)
	{
		unset($this->resolved[$engine]);

		$this->resolvers[$engine] = $resolver;
	}

	/**
	 * Resolver an engine instance by name.
	 *
	 * @param  string  $engine
	 * @return \Milky\Http\View\Engines\EngineInterface
	 * @throws \InvalidArgumentException
	 */
	public function resolve($engine)
	{
		if (isset($this->resolved[$engine])) {
			return $this->resolved[$engine];
		}

		if (isset($this->resolvers[$engine])) {
			return $this->resolved[$engine] = call_user_func($this->resolvers[$engine]);
		}

		throw new InvalidArgumentException("Engine $engine not found.");
	}
}
