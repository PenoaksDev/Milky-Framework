<?php namespace Milky\Exceptions\Filters;

use Exception;
use Milky\Exceptions\Displayers\DisplayerInterface;
use Milky\Http\Request;

/**
 * This is the filter interface.
 */
interface FilterInterface
{
	/**
	 * Filter and return the displayers.
	 *
	 * @param DisplayerInterface[] $displayers
	 * @param Request $request
	 * @param \Exception $original
	 * @param \Exception $transformed
	 * @param int $code
	 *
	 * @return DisplayerInterface[]
	 */
	public function filter( array $displayers, Request $request, Exception $original, Exception $transformed, $code );
}
