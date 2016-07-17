<?php
namespace Penoaks\Framework\Exceptions;

use Exception;
use HttpResponseException;
use Penoaks\Auth\Access\AuthorizationException;
use Penoaks\Barebones\ExceptionHandler;
use Penoaks\Bindings\Bindings;
use Penoaks\Database\Eloquent\ModelNotFoundException;
use Penoaks\Http\Request;
use Penoaks\Http\Response;
use Penoaks\Validation\ValidationException;
use Symfony\Component\Console\Application;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Debug\ExceptionHandler as SymfonyExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class Handler implements ExceptionHandler
{
	/**
	 * A list of the exception types that should not be reported.
	 *
	 * @var array
	 */
	protected $dontReport = [
		AuthorizationException::class,
		HttpException::class,
		ModelNotFoundException::class,
		ValidationException::class,
	];

	/**
	 * Report or log an exception.
	 *
	 * @param  \Exception $e
	 * @return void
	 */
	public function report( Exception $e )
	{
		if ( $this->shouldntReport( $e ) )
		{
			return;
		}
		try
		{
			$logger = Bindings::get( 'Psr\Log\LoggerInterface' );
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
		foreach ( $this->dontReport as $type )
			if ( $e instanceof $type )
				return true;

		return false;
	}

	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param  Request $request
	 * @param  \Exception $e
	 * @return Response
	 */
	public function render( $request, Exception $e )
	{
		if ( $e instanceof HttpResponseException )
		{
			return $e->getResponse();
		}
		elseif ( $e instanceof ModelNotFoundException )
		{
			$e = new NotFoundHttpException( $e->getMessage(), $e );
		}
		elseif ( $e instanceof AuthorizationException )
		{
			$e = new HttpException( 403, $e->getMessage() );
		}
		elseif ( $e instanceof ValidationException && $e->getResponse() )
		{
			return $e->getResponse();
		}
		$fe = FlattenException::create( $e );
		$handler = new SymfonyExceptionHandler( env( 'APP_DEBUG', false ) );
		$decorated = $this->decorate( $handler->getContent( $fe ), $handler->getStylesheet( $fe ) );
		$response = new Response( $decorated, $fe->getStatusCode(), $fe->getHeaders() );
		$response->exception = $e;

		return $response;
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
            /* Copyright (c) 2010, Yahoo! Inc. All rights reserved. Code licensed under the BSD License: http://developer.yahoo.com/yui/license.html */
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
	 * Render an exception to the console.
	 *
	 * @param  \Symfony\Component\Console\Output\OutputInterface $output
	 * @param  \Exception $e
	 * @return void
	 */
	public function renderForConsole( $output, Exception $e )
	{
		( new Application )->renderException( $e, $output );
	}
}
