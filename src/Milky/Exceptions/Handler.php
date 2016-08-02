<?php namespace Milky\Exceptions;

use Exception;
use Milky\Binding\UniversalBuilder;
use Milky\Console\ConsoleFactory;
use Milky\Exceptions\Displayers\DisplayerInterface;
use Milky\Exceptions\Http\HttpResponseException;
use Milky\Exceptions\Validation\ValidationException;
use Milky\Facades\Config;
use Milky\Framework;
use Milky\Http\HttpFactory;
use Milky\Http\Request;
use Milky\Http\Response;
use Milky\Impl\Extendable;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler
{
	use Extendable;

	/**
	 * A list of the Exception types that should not be reported.
	 *
	 * @var array
	 */
	private $dontReport = [
		NotFoundHttpException::class,
		HttpResponseException::class,
		ValidationException::class,
	];

	public function __construct()
	{
		if ( Framework::fw()->environment( 'production' ) )
		{
			error_reporting( -1 );
			ini_set( 'display_errors', 'Off' );
		}

		set_error_handler( [$this, 'handleError'] );

		set_exception_handler( [$this, 'handleException'] );

		register_shutdown_function( [$this, 'handleShutdown'] );
	}

	/**
	 * Adds an exception to not be reported
	 *
	 * @param array|string $exceptions
	 */
	public function dontReport( $exceptions )
	{
		$this->dontReport = array_merge( $this->dontReport, is_array( $exceptions ) ? $exceptions : [$exceptions] );
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
			throw new \ErrorException( $message, 0, $level, $file, $line );
	}

	/**
	 * Handle an uncaught exception from the application.
	 *
	 * Note: Most exceptions can be handled via the try / catch block in
	 * the HTTP and Console kernels. But, fatal error exceptions must
	 * be handled differently since they are not normal exceptions.
	 *
	 * @param  \Throwable $e
	 */
	public function handleException( $e )
	{
		if ( !$e instanceof Exception )
			$e = new FatalThrowableError( $e );

		$this->report( $e );

		if ( ConsoleFactory::runningInConsole() )
			UniversalBuilder::resolve( 'console' )->renderException( new ConsoleOutput, $e );
		else
			$this->render( HttpFactory::i()->request(), $e )->send();
	}

	/**
	 * Handle the PHP shutdown event.
	 */
	public function handleShutdown()
	{
		if ( !is_null( $error = error_get_last() ) && in_array( $error['type'], [
				E_ERROR,
				E_CORE_ERROR,
				E_COMPILE_ERROR,
				E_PARSE
			] )
		)
		{
			$this->handleException( new FatalErrorException( $error['message'], $error['type'], 0, $error['file'], $error['line'], 0 ) );
		}
	}

	/**
	 * Report or log an Exception.
	 *
	 * @param  Exception $e
	 */
	public function report( Exception $e )
	{
		if ( !$this->shouldReport( $e ) )
			return;

		try
		{
			$logger = Framework::log();
		}
		catch ( Exception $ex )
		{
			throw $e; // throw the original Exception
		}

		$logger->error( $e );


		$level = $this->getLevel( $e );
		$id = UniversalBuilder::resolveClass( ExceptionIdentifier::class )->identify( $e );

		$logger->{$level}( $e, ['identification' => ['id' => $id]] );
	}

	/**
	 * Get the exception level.
	 *
	 * @param \Exception $exception
	 *
	 * @return string
	 */
	protected function getLevel( Exception $exception )
	{
		foreach ( array_get( $this->getConfig(), 'levels', [] ) as $class => $level )
			if ( $exception instanceof $class )
				return $level;

		return 'error';
	}

	/**
	 * Determine if the Exception is in the "do not report" list.
	 *
	 * @param  Exception $e
	 * @return bool
	 */
	protected function shouldReport( Exception $e )
	{
		foreach ( $this->dontReport as $type )
			if ( $e instanceof $type )
				return false;

		return true;
	}

	/**
	 * Render an Exception into a response.
	 *
	 * @param  Request $request
	 * @param  Exception $e
	 * @return Response
	 */
	public function render( $request, Exception $e )
	{
		$transformed = $this->getTransformed( $e );

		$response = method_exists( $e, 'getResponse' ) ? $e->getResponse() : null;

		if ( !$response instanceof Response )
			try
			{
				$response = $this->getResponse( $request, $e, $transformed );
			}
			catch ( Exception $e )
			{
				$this->report( $e );
				$response = new Response( 'Internal server error.', 500 );
			}

		return $this->toHttpResponse( $response, $transformed );
	}

	/**
	 * Map Exception into an response.
	 *
	 * @param  Response $response
	 * @param  Exception $e
	 * @return Response
	 */
	protected function toHttpResponse( $response, Exception $e )
	{
		$response = new Response( $response->getContent(), $response->getStatusCode(), $response->headers->all() );

		return $response->withException( $e );
	}

	/**
	 * Get exceptions configuration
	 *
	 * @return array
	 */
	public function getConfig( $key = null, $def = null )
	{
		return Config::get( 'exceptions' . ( empty( $key ) ? "" : "." . $key ), $def );
	}

	/**
	 * Get the appropriate response object.
	 *
	 * @param Request $request
	 * @param \Exception $transformed
	 * @param \Exception $exception
	 *
	 * @return Response
	 */
	protected function getResponse( Request $request, Exception $exception, Exception $transformed )
	{
		$id = UniversalBuilder::resolve( 'exceptions.identifier' )->identify( $exception );

		$flattened = FlattenException::create( $transformed );
		$code = $flattened->getStatusCode();
		$headers = $flattened->getHeaders();

		return $this->getDisplayer( $request, $exception, $transformed, $code )->display( $transformed, $id, $code, $headers );
	}

	/**
	 * Get the transformed exception.
	 *
	 * @param \Exception $exception
	 *
	 * @return \Exception
	 */
	protected function getTransformed( Exception $exception )
	{
		foreach ( $this->make( array_get( $this->getConfig(), 'transformers', [] ) ) as $transformer )
		{
			var_dump( $transformer );
			$exception = $transformer->transform( $exception );
		}

		return $exception;
	}

	/**
	 * Get the displayer instance.
	 *
	 * @param Request $request
	 * @param \Exception $original
	 * @param \Exception $transformed
	 * @param int $code
	 *
	 * @return DisplayerInterface
	 */
	protected function getDisplayer( Request $request, Exception $original, Exception $transformed, $code )
	{
		$displayers = $this->make( $this->getConfig( 'displayers', [] ) );

		if ( $filtered = $this->getFiltered( $displayers, $request, $original, $transformed, $code ) )
			return $filtered[0];

		$def = $this->getConfig( 'default' );

		return UniversalBuilder::resolveClass( $def );
	}

	/**
	 * Get the filtered list of displayers.
	 *
	 * @param DisplayerInterface[] $displayers
	 * @param Request $request
	 * @param \Exception $original
	 * @param \Exception $transformed
	 * @param int $code
	 *
	 * @return DisplayerInterface[]
	 */
	protected function getFiltered( array $displayers, Request $request, Exception $original, Exception $transformed, $code )
	{
		foreach ( $this->make( $this->getConfig( 'filters', [] ) ) as $filter )
			$displayers = $filter->filter( $displayers, $request, $original, $transformed, $code );

		return array_values( $displayers );
	}

	/**
	 * Make multiple objects using the container.
	 *
	 * @param string [] $classes
	 *
	 * @return object[]
	 */
	protected function make( array $classes )
	{
		foreach ( $classes as $index => $class )
			try
			{
				$classes[$index] = UniversalBuilder::resolveClass( $class, true );
			}
			catch ( Exception $e )
			{
				unset( $classes[$index] );
				$this->report( $e );
			}

		return array_values( $classes );
	}

	/**
	 * @return $this
	 */
	public static function i()
	{
		return UniversalBuilder::resolve( 'exceptions.handler' );
	}
}
