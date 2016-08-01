<?php namespace Milky\Exceptions;

use Exception;
use Milky\Binding\BindingBuilder;
use Milky\Exceptions\Http\HttpResponseException;
use Milky\Facades\Config;
use Milky\Facades\View;
use Milky\Framework;
use Milky\Http\Request;
use Milky\Http\Response;
use Milky\Http\Routing\ResponseFactory;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Debug\ExceptionHandler as SymfonyExceptionHandler;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler
{
	/**
	 * @var ExceptionIdentifier
	 */
	private $identifier;

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
		$id = $this->identifier()->identify( $e );

		$logger->{$level}( $e, ['identification' => ['id' => $id]] );
	}

	public function identifier()
	{
		return $this->identifier ?: $this->identifier = new ExceptionIdentifier();
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

		return $this->toIlluminateResponse( $response, $transformed );
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
	 * Render an Exception to the console.
	 *
	 * @param  OutputInterface $output
	 * @param  Exception $e
	 * @return void
	 */
	public function renderForConsole( $output, Exception $e )
	{
		( new ConsoleApplication )->renderException( $e, $output );
	}

	/**
	 * Render the given HttpException.
	 *
	 * @param  HttpException $e
	 * @return Response
	 */
	protected function renderHttpException( HttpException $e )
	{
		$status = $e->getStatusCode();

		if ( View::exists( "errors." . $status ) )
			return ResponseFactory::i()->view( "errors.{$status}", ['exception' => $e], $status, $e->getHeaders() );
		else
			return $this->convertExceptionToResponse( $e );
	}

	/**
	 * Create a Symfony response for the given Exception.
	 *
	 * @param  Exception $e
	 * @return Response
	 */
	protected function convertExceptionToResponse( Exception $e )
	{
		$e = FlattenException::create( $e );

		$handler = new SymfonyExceptionHandler( Config::get( 'app.debug' ) );

		$decorated = $this->decorate( $handler->getContent( $e ), $handler->getStylesheet( $e ) );

		return SymfonyResponse::create( $decorated, $e->getStatusCode(), $e->getHeaders() );
	}

	/**
	 * Convert an authentication Exception into an unauthenticated response.
	 *
	 * @param  Request $request
	 * @param  AuthenticationException $e
	 * @return Response
	 */
	protected function unauthenticated( $request, AuthenticationException $e )
	{
		if ( $request->ajax() || $request->wantsJson() )
			return ResponseFactory::i()->make( 'Unauthorized.', 401 );
		else
			return redirect()->guest( 'login' );
	}

	/**
	 * Get the html response content.
	 *
	 * @param  string $content
	 * @param  string $css
	 * @return string
	 */
	protected function decorate( $content, $css )
	{
		return <<<EOF
<!DOCTYPE html>
<html>
	<head>
		<meta name="robots" content="noindex,nofollow" />
		<style>
			html{color:#000;background:#FFF;}body,div,dl,dt,dd,ul,ol,li,h1,h2,h3,h4,h5,h6,pre,code,form,fieldset,legend,input,textarea,p,blockquote,th,td{margin:0;padding:0;}table{border-collapse:collapse;border-spacing:0;}fieldset,img{border:0;}address,caption,cite,code,dfn,em,strong,th,var{font-style:normal;font-weight:normal;}li{list-style:none;}caption,th{text-align:left;}h1,h2,h3,h4,h5,h6{font-size:100%;font-weight:normal;}q:before,q:after{content:'';}abbr,acronym{border:0;font-variant:normal;}sup{vertical-align:text-top;}sub{vertical-align:text-bottom;}input,textarea,select{font-family:inherit;font-size:inherit;font-weight:inherit;}input,textarea,select{*font-size:100%;}legend{color:#000;}
			html { background: #eee; padding: 10px }
			img { border: 0; }
			#sf-resetcontent { width:970px; margin:0 auto; }
			$css
		</style>
	</head>
	<body>
		$content
	</body>
</html>
EOF;
	}

	/**
	 * Determine if the given Exception is an HTTP Exception.
	 *
	 * @param  Exception $e
	 * @return bool
	 */
	protected function isHttpException( Exception $e )
	{
		return $e instanceof HttpException;
	}

	/**
	 * Get exceptions configuration
	 *
	 * @return array
	 */
	public function getConfig()
	{
		return Config::get( 'exceptions' );
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
		$id = $this->container->make( ExceptionIdentifier::class )->identify( $exception );

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
		foreach ( $this->make( array_get( $this->config, 'transformers', [] ) ) as $transformer )
		{
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
		$displayers = $this->make( array_get( $this->config, 'displayers', [] ) );

		if ( $filtered = $this->getFiltered( $displayers, $request, $original, $transformed, $code ) )
		{
			return $filtered[0];
		}

		return $this->container->make( array_get( $this->config, 'default' ) );
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
		foreach ( $this->make( array_get( $this->getConfig(), 'filters', [] ) ) as $filter )
		{
			$displayers = $filter->filter( $displayers, $request, $original, $transformed, $code );
		}

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
		{
			try
			{
				$classes[$index] = BindingBuilder::resolveBinding( $class );
			}
			catch ( Exception $e )
			{
				unset( $classes[$index] );
				$this->report( $e );
			}
		}

		return array_values( $classes );
	}
}
