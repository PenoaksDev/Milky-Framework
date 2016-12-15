<?php namespace Milky\Http\Middleware;

use Closure;
use Milky\Encryption\Encrypter;
use Milky\Exceptions\Session\TokenMismatchException;
use Milky\Facades\Config;
use Milky\Http\Request;
use Milky\Http\Response;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class VerifyCsrfToken
{
	/**
	 * The encrypter implementation.
	 *
	 * @var Encrypter
	 */
	protected $encrypter;

	/**
	 * The URIs that should be excluded from CSRF verification.
	 *
	 * @var array
	 */
	protected $except = [];

	/**
	 * Create a new middleware instance.
	 *
	 * @param  Encrypter $encrypter
	 * @return void
	 */
	public function __construct( Encrypter $encrypter )
	{
		$this->encrypter = $encrypter;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  Request $request
	 * @param  \Closure $next
	 * @return mixed
	 *
	 * @throws TokenMismatchException
	 */
	public function handle( $request, Closure $next )
	{
		if ( $this->isReading( $request ) || $this->shouldPassThrough( $request ) || $this->tokensMatch( $request ) )
			return $this->addCookieToResponse( $request, $next( $request ) );

		throw new TokenMismatchException;
	}

	/**
	 * Determine if the request has a URI that should pass through CSRF verification.
	 *
	 * @param  Request $request
	 * @return bool
	 */
	protected function shouldPassThrough( $request )
	{
		foreach ( $this->except as $except )
		{
			if ( $except !== '/' )
				$except = trim( $except, '/' );
			if ( $request->is( $except ) )
				return true;
		}

		return false;
	}

	/**
	 * Determine if the session and input CSRF tokens match.
	 *
	 * @param  Request $request
	 * @return bool
	 */
	protected function tokensMatch( $request )
	{
		$sessionToken = $request->session()->token();
		$token = $request->input( '_token' ) ?: $request->header( 'X-CSRF-TOKEN' );
		if ( !$token && $header = $request->header( 'X-XSRF-TOKEN' ) )
		{
			$token = $this->encrypter->decrypt( $header );
		}
		if ( !is_string( $sessionToken ) || !is_string( $token ) )
		{
			return false;
		}

		return hash_equals( $sessionToken, $token );
	}

	/**
	 * Add the CSRF token to the response cookies.
	 *
	 * @param  Request $request
	 * @param  Response $response
	 * @return Response
	 */
	protected function addCookieToResponse( $request, $response )
	{
		$config = Config::get( 'session' );
		$response->headers->setCookie( new Cookie( 'XSRF-TOKEN', $request->session()->token(), time() + 60 * 120, $config['path'], $config['domain'], $config['secure'], false ) );

		return $response;
	}

	/**
	 * Determine if the HTTP request uses a ‘read’ verb.
	 *
	 * @param  Request $request
	 * @return bool
	 */
	protected function isReading( $request )
	{
		return in_array( $request->method(), ['HEAD', 'GET', 'OPTIONS'] );
	}
}
