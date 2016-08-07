<?php namespace Milky\Http\Middleware;

use Closure;
use Milky\Http\Cookies\CookieJar;
use Milky\Http\Request;

class AddQueuedCookiesToResponse
{
	/**
	 * The cookie jar instance.
	 *
	 * @var CookieJar
	 */
	protected $cookies;

	/**
	 * Create a new CookieQueue instance.
	 *
	 * @param  CookieJar $cookies
	 */
	public function __construct( CookieJar $cookies )
	{
		$this->cookies = $cookies;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  Request $request
	 * @param  \Closure $next
	 * @return mixed
	 */
	public function handle( $request, Closure $next )
	{
		$response = $next( $request );

		foreach ( $this->cookies->getQueuedCookies() as $cookie )
			$response->headers->setCookie( $cookie );

		return $response;
	}
}
