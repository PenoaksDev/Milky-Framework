<?php

namespace Foundation\Cookie\Middleware;

use Closure;
use Foundation\Contracts\Cookie\QueueingFactory as CookieJar;

class AddQueuedCookiesToResponse
{
	/**
	 * The cookie jar instance.
	 *
	 * @var \Foundation\Contracts\Cookie\QueueingFactory
	 */
	protected $cookies;

	/**
	 * Create a new CookieQueue instance.
	 *
	 * @param  \Foundation\Contracts\Cookie\QueueingFactory  $cookies
	 * @return void
	 */
	public function __construct(CookieJar $cookies)
	{
		$this->cookies = $cookies;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Foundation\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		$response = $next($request);

		foreach ($this->cookies->getQueuedCookies() as $cookie)
{
			$response->headers->setCookie($cookie);
		}

		return $response;
	}
}
