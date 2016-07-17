<?php
namespace Penoaks\Bootstrap;

use ErrorException;
use Exception;
use Penoaks\Barebones\Bootstrap;
use Penoaks\Barebones\ExceptionHandler;
use Penoaks\Facades\Log;
use Penoaks\Framework;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\Debug\Exception\FatalThrowableError;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class HandleExceptions implements Bootstrap
{
	/**
	 * The application instance.
	 *
	 * @var \Penoaks\Framework
	 */
	protected $fw;

	/**
	 * Bootstrap the given application.
	 *
	 * @param  \Penoaks\Framework $fw
	 * @return void
	 */
	public function boot( Framework $fw )
	{
		$this->fw = $fw;

		error_reporting( -1 );

		set_error_handler( [$this, 'handleError'] );

		set_exception_handler( [$this, 'handleException'] );

		register_shutdown_function( [$this, 'handleShutdown'] );

		if ( !$fw->environment( 'production' ) )
		{
			ini_set( 'display_errors', 'On' );
		}
	}

	/**
	 * Convert a PHP error to an ErrorException.
	 *
	 * @param  int $level
	 * @param  string $message
	 * @param  string $file
	 * @param  int $line
	 * @param  array $context
	 * @return void
	 *
	 * @throws \ErrorException
	 */
	public function handleError( $level, $message, $file = '', $line = 0, $context = [] )
	{
		if ( error_reporting() & $level )
			throw new ErrorException( $message, 0, $level, $file, $line );
	}

	/**
	 * Handle an uncaught exception from the application.
	 *
	 * Note: Most exceptions can be handled via the try / catch block in
	 * the HTTP and Console kernels. But, fatal error exceptions must
	 * be handled differently since they are not normal exceptions.
	 *
	 * @param  \Throwable $e
	 * @return void
	 */
	public function handleException( $e )
	{
		try
		{
			if ( !$e instanceof Exception )
			{
				$e = new FatalThrowableError( $e );
			}

			$this->getExceptionHandler()->report( $e );

			if ( $this->fw->runningInConsole() )
			{
				$this->renderForConsole( $e );
			}
			else
			{
				$this->renderHttpResponse( $e );
			}
		}
		catch ( \Throwable $t )
		{
			Log::critical( "Failed to handle exception with exception: " . $t->getMessage() );
			throw $e; // Forward exception, we failed!
		}
	}

	/**
	 * Render an exception to the console.
	 *
	 * @param  \Exception $e
	 * @return void
	 */
	protected function renderForConsole( Exception $e )
	{
		$this->getExceptionHandler()->renderForConsole( new ConsoleOutput, $e );
	}

	/**
	 * Render an exception as an HTTP response and send it.
	 *
	 * @param  \Exception $e
	 * @return void
	 */
	protected function renderHttpResponse( Exception $e )
	{
		$this->getExceptionHandler()->render( $this->fw->bindings['request'], $e )->send();
	}

	/**
	 * Handle the PHP shutdown event.
	 *
	 * @return void
	 */
	public function handleShutdown()
	{
		if ( !is_null( $error = error_get_last() ) && $this->isFatal( $error['type'] ) )
			$this->handleException( $this->fatalExceptionFromError( $error, 0 ) );
	}

	/**
	 * Create a new fatal exception instance from an error array.
	 *
	 * @param  array $error
	 * @param  int|null $traceOffset
	 * @return \Symfony\Component\Debug\Exception\FatalErrorException
	 */
	protected function fatalExceptionFromError( array $error, $traceOffset = null )
	{
		return new FatalErrorException( $error['message'], $error['type'], 0, $error['file'], $error['line'], $traceOffset );
	}

	/**
	 * Determine if the error type is fatal.
	 *
	 * @param  int $type
	 * @return bool
	 */
	protected function isFatal( $type )
	{
		return in_array( $type, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE] );
	}

	/**
	 * Get an instance of the exception handler.
	 *
	 * @return ExceptionHandler
	 */
	protected function getExceptionHandler()
	{
		return $this->fw->bindings->make( 'Penoaks\Barebones\ExceptionHandler' );
	}
}
