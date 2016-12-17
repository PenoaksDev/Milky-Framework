<?php namespace Milky\Exceptions\Displayers;

use Exception;
use Milky\Exceptions\View\FatalViewError;
use Symfony\Component\HttpFoundation\Response;
use Whoops\Handler\PrettyPageHandler as Handler;
use Whoops\Run as Whoops;

/**
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class DebugDisplayer implements DisplayerInterface
{
	/**
	 * Get the error response associated with the given exception.
	 *
	 * @param \Exception $exception
	 * @param string $id
	 * @param int $code
	 * @param string[] $headers
	 *
	 * @return Response
	 */
	public function display( Exception $exception, $id, $code, array $headers )
	{
		$content = $this->whoops()->handleException( $exception );

		return new Response( $content, $code, array_merge( $headers, ['Content-Type' => 'text/html'] ) );
	}

	/**
	 * Get the whoops instance.
	 *
	 * @return Whoops
	 */
	protected function whoops()
	{
		$whoops = new Whoops();
		$whoops->allowQuit( false );
		$whoops->writeToOutput( false );
		$whoops->pushHandler( new Handler() );

		return $whoops;
	}

	/**
	 * Get the supported content type.
	 *
	 * @return string
	 */
	public function contentType()
	{
		return 'text/html';
	}

	/**
	 * Can we display the exception?
	 *
	 * @param \Exception $original
	 * @param \Exception $transformed
	 * @param int $code
	 *
	 * @return bool
	 */
	public function canDisplay( Exception $original, Exception $transformed, $code )
	{
		return class_exists( Whoops::class );
	}

	/**
	 * Do we provide verbose information about the exception?
	 *
	 * @return bool
	 */
	public function isVerbose()
	{
		return true;
	}
}
