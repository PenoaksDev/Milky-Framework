<?php namespace Milky\Http\Middleware;

use Closure;
use Milky\Http\Request;
use Milky\Http\Response;

class FrameGuard
{
	/**
	 * Handle the given request and get the response.
	 *
	 * @param  Request $request
	 * @param  \Closure $next
	 * @return Response
	 */
	public function handle( $request, Closure $next )
	{
		$response = $next( $request );

		$response->headers->set( 'X-Frame-Options', 'SAMEORIGIN', false );

		return $response;
	}
}
