<?php
namespace Penoaks\Http\Middleware;

use Closure;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Session\TokenMismatchException;
use Penoaks\Facades\Config;
use Penoaks\Framework;
use Symfony\Component\HttpFoundation\Cookie;

class VerifyCsrfToken
{
	/**
	 * The application instance.
	 *
	 * @var \Penoaks\Framework
	 */
	protected $fw;

	/**
	 * The encrypter implementation.
	 *
	 * @var \Penoaks\Contracts\Encryption\Encrypter
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
	 * @param  \Penoaks\Framework $fw
	 * @param  \Penoaks\Contracts\Encryption\Encrypter $encrypter
	 * @return void
	 */
	public function __construct( Framework $fw, Encrypter $encrypter )
	{
		$this->fw = $fw;
		$this->encrypter = $encrypter;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Milky\Http\Routing\Request $request
	 * @param  \Closure $next
	 * @return mixed
	 *
	 * @throws \Penoaks\Session\TokenMismatchException
	 */
	public function handle( $request, Closure $next )
	{
		if ( $this->isReading( $request ) || $this->runningUnitTests() || $this->shouldPassThrough( $request ) || $this->tokensMatch( $request ) )
		{
			return $this->addCookieToResponse( $request, $next( $request ) );
		}

		throw new TokenMismatchException;
	}

	/**
	 * Determine if the request has a URI that should pass through CSRF verification.
	 *
	 * @param  \Milky\Http\Routing\Request $request
	 * @return bool
	 */
	protected function shouldPassThrough( $request )
	{
		foreach ( $this->except as $except )
		{
			if ( $except !== '/' )
			{
				$except = trim( $except, '/' );
			}

			if ( $request->is( $except ) )
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if the application is running unit tests.
	 *
	 * @return bool
	 */
	protected function runningUnitTests()
	{
		return $this->fw->runningInConsole() && $this->fw->runningUnitTests();
	}

	/**
	 * Determine if the session and input CSRF tokens match.
	 *
	 * @param  \Milky\Http\Routing\Request $request
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
	 * @param  \Milky\Http\Routing\Request $request
	 * @param  \Penoaks\Http\Response $response
	 * @return \Penoaks\Http\Response
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
	 * @param  \Milky\Http\Routing\Request $request
	 * @return bool
	 */
	protected function isReading( $request )
	{
		return in_array( $request->method(), ['HEAD', 'GET', 'OPTIONS'] );
	}
}
