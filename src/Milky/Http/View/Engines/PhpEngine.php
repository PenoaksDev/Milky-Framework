<?php namespace Milky\Http\View\Engines;

use Exception;
use Throwable;

/**
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class PhpEngine implements EngineInterface
{
	/**
	 * Get the evaluated contents of the view.
	 *
	 * @param  string $path
	 * @param  array $data
	 * @return string
	 */
	public function get( $path, array $data = [] )
	{
		return $this->evaluatePath( $path, $data );
	}

	/**
	 * Get the evaluated contents of the view at the given path.
	 *
	 * @param  string $__path
	 * @param  array $__data
	 * @return string
	 */
	protected function evaluatePath( $__path, $__data )
	{
		$obLevel = ob_get_level();

		ob_start();

		extract( $__data, EXTR_SKIP );

		// We'll evaluate the contents of the view inside a try/catch block so we can
		// flush out any stray output that might get out before an error occurs or
		// an exception is thrown. This prevents any partial views from leaking.
		try
		{
			include $__path;
		}
		catch ( Exception $e )
		{
			$this->handleViewException0( $e, $obLevel );
		}
		catch ( Throwable $e )
		{
			$this->handleViewException0( $e, $obLevel );
		}

		return ltrim( ob_get_clean() );
	}

	private function handleViewException0( Throwable $t, $obLevel )
	{
		while ( ob_get_level() > $obLevel )
			ob_end_clean();

		$this->handleViewException( $t );
	}

	/**
	 * Handle a view exception.
	 *
	 * @param  \Exception $e
	 * @param  int $obLevel
	 * @return void
	 *
	 * @throws $e
	 */
	protected function handleViewException( Throwable $t )
	{
		// XXX I think there is know issues with throwing Throwables in older version of PHP, might want to wrap the throwable for such cases.

		throw $t;
	}
}
