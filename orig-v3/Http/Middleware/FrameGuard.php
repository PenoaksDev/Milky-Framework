<?php

namespace Penoaks\Http\Middleware;

use Closure;

class FrameGuard
{
	/**
	 * Handle the given request and get the response.
	 *
	 * @param  \Milky\Http\Routing\Request  $request
	 * @param  \Closure  $next
	 * @return \Penoaks\Http\Response
	 */
	public function handle($request, Closure $next)
	{
		$response = $next($request);

		$response->headers->set('X-Frame-Options', 'SAMEORIGIN', false);

		return $response;
	}
}
