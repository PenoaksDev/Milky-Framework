<?php namespace Milky\Http\Middleware;

use Closure;
use Milky\Exceptions\Http\HttpException;
use Milky\Framework;
use Milky\Http\Request;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class CheckForMaintenanceMode
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  Request $request
	 * @param  \Closure $next
	 *
	 * @return mixed
	 *
	 * @throws HttpException
	 */
	public function handle( $request, Closure $next )
	{
		if ( Framework::fw()->isDownForMaintenance() )
			throw new HttpException( 503 );

		return $next( $request );
	}
}
