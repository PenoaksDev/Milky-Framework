<?php namespace Milky\Exceptions\Filters;

use Exception;
use Milky\Exceptions\Displayers\DisplayerInterface;
use Milky\Http\Request;

/**
 * This is the verbose filter class.
 */
class VerboseFilter
{
	/**
	 * Is debug mode enabled?
	 *
	 * @var bool
	 */
	protected $debug;

	/**
	 * Create a new verbose filter instance.
	 *
	 * @param bool $debug
	 */
	public function __construct( $debug )
	{
		$this->debug = $debug;
	}

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
		if ( $this->debug !== true )
			foreach ( $displayers as $index => $displayer )
				if ( $displayer->isVerbose() )
					unset( $displayers[$index] );

		return array_values( $displayers );
	}
}
