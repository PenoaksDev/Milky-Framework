<?php

namespace Foundation\Http\Middleware;

use Closure;

class FrameGuard
{
	/**
	 * Handle the given request and get the response.
	 *
	 * @param  \Foundation\Http\Request  $request
	 * @param  \Closure  $next
	 * @return \Foundation\Http\Response
	 */
	public function handle($request, Closure $next)
	{
		$response = $next($request);

		$response->headers->set('X-Frame-Options', 'SAMEORIGIN', false);

		return $response;
	}
}
