<?php namespace Milky\Exceptions\Filters;

use Exception;
use Milky\Exceptions\Displayers\DisplayerInterface;
use Milky\Http\Request;

/**
 * This is the can display filter class.
 */
class CanDisplayFilter
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
	public function filter( array $displayers, Request $request, Exception $original, Exception $transformed, $code )
	{
		foreach ( $displayers as $index => $displayer )
		{
			if ( !$displayer->canDisplay( $original, $transformed, $code ) )
			{
				unset( $displayers[$index] );
			}
		}

		return array_values( $displayers );
	}
}
