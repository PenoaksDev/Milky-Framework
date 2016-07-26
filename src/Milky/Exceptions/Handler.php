<?php namespace Milky\Exceptions;

use Exception;
use Milky\Auth\Access\AuthorizationException;
use Milky\Auth\AuthenticationException;
use Milky\Database\Eloquent\ModelNotFoundException;
use Milky\Facades\Config;
use Milky\Facades\View;
use Milky\Framework;
use Milky\Http\Request;
use Milky\Http\Response;
use Milky\Facades\Response as ResponseFacade;
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
	 * A list of the exception types that should not be reported.
	 *
	 * @var array
	 */
	protected $dontReport = [];

	/**
	 * Report or log an exception.
	 *
	 * @param  \Exception $e
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
			throw $e; // throw the original exception
		}

		$logger->error( $e );
	}

	/**
	 * Determine if the exception should be reported.
	 *
	 * @param  \Exception $e
	 * @return bool
	 */
	public function shouldReport( Exception $e )
	{
		return !$this->shouldntReport( $e );
	}

	/**
	 * Determine if the exception is in the "do not report" list.
	 *
	 * @param  \Exception $e
	 * @return bool
	 */
	protected function shouldntReport( Exception $e )
	{
		$dontReport = array_merge( $this->dontReport, [HttpResponseException::class] );

		foreach ( $dontReport as $type )
		{
			if ( $e instanceof $type )
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Render an exception into a response.
	 *
	 * @param  Request $request
	 * @param  \Exception $e
	 * @return Response
	 */
	public function render( $request, Exception $e )
	{
		try
		{
			if ( $e instanceof HttpResponseException )
			{
				return $e->getResponse();
			}
			elseif ( $e instanceof ModelNotFoundException )
			{
				$e = new NotFoundHttpException( $e->getMessage(), $e );
			}
			elseif ( $e instanceof AuthenticationException )
			{
				return $this->unauthenticated( $request, $e );
			}
			elseif ( $e instanceof AuthorizationException )
			{
				$e = new HttpException( 403, $e->getMessage() );
			}
			elseif ( $e instanceof ValidationException && $e->getResponse() )
			{
				return $e->getResponse();
			}

			if ( $this->isHttpException( $e ) )
			{
				return $this->toHttpResponse( $this->renderHttpException( $e ), $e );
			}
			else
			{
				return $this->toHttpResponse( $this->convertExceptionToResponse( $e ), $e );
			}
		}
		catch ( \Throwable $t )
		{
			throw $e;
		}
	}

	/**
	 * Map exception into an illuminate response.
	 *
	 * @param  Response $response
	 * @param  \Exception $e
	 * @return Response
	 */
	protected function toHttpResponse( $response, Exception $e )
	{
		$response = new Response( $response->getContent(), $response->getStatusCode(), $response->headers->all() );

		return $response->withException( $e );
	}

	/**
	 * Render an exception to the console.
	 *
	 * @param  OutputInterface $output
	 * @param  \Exception $e
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

		if ( View::i()->exists( "errors.{$status}" ) )
		{
			return ResponseFacade::view( "errors.{$status}", ['exception' => $e], $status, $e->getHeaders() );
		}
		else
		{
			return $this->convertExceptionToResponse( $e );
		}
	}

	/**
	 * Create a Symfony response for the given exception.
	 *
	 * @param  \Exception $e
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
	 * Convert an authentication exception into an unauthenticated response.
	 *
	 * @param  Request $request
	 * @param  AuthenticationException $e
	 * @return Response
	 */
	protected function unauthenticated( $request, AuthenticationException $e )
	{
		if ( $request->ajax() || $request->wantsJson() )
		{
			return response( 'Unauthorized.', 401 );
		}
		else
		{
			return redirect()->guest( 'login' );
		}
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
	 * Determine if the given exception is an HTTP exception.
	 *
	 * @param  \Exception $e
	 * @return bool
	 */
	protected function isHttpException( Exception $e )
	{
		return $e instanceof HttpException;
	}
}
