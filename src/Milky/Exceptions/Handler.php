<?php namespace Milky\Exceptions;

use Exception;
use Milky\Binding\UniversalBuilder;
use Milky\Exceptions\Displayers\DisplayerInterface;
use Milky\Exceptions\Http\HttpResponseException;
use Milky\Facades\Config;
use Milky\Framework;
use Milky\Http\Request;
use Milky\Http\Response;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler
{
	/**
	 * A list of the Exception types that should not be reported.
	 *
	 * @var array
	 */
	protected $dontReport = [
		NotFoundHttpException::class,
	];

	/**
	 * Report or log an Exception.
	 *
	 * @param  Exception $e
	 * @return void
	 */
	public function report( Exception $e )
	{
		if ( $this->shouldntReport( $e ) )
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
	 * Determine if the Exception should be reported.
	 *
	 * @param  Exception $e
	 * @return bool
	 */
	public function shouldReport( Exception $e )
	{
		return !$this->shouldntReport( $e );
	}

	/**
	 * Determine if the Exception is in the "do not report" list.
	 *
	 * @param  Exception $e
	 * @return bool
	 */
	protected function shouldntReport( Exception $e )
	{
		$dontReport = array_merge( $this->dontReport, [HttpResponseException::class] );

		foreach ( $dontReport as $type )
			if ( $e instanceof $type )
				return true;

		return false;
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
	 * Get the approprate response object.
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
}
