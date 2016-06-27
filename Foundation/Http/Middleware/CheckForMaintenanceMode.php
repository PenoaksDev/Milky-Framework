<?php

namespace Foundation\Http\Middleware;

use Closure;
use Foundation\Contracts\Foundation\Application;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CheckForMaintenanceMode
{
	/**
	 * The application implementation.
	 *
	 * @var \Foundation\Contracts\Foundation\Application
	 */
	protected $app;

	/**
	 * Create a new middleware instance.
	 *
	 * @param  \Foundation\Contracts\Foundation\Application  $app
	 * @return void
	 */
	public function __construct(Application $app)
	{
		$this->app = $app;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Foundation\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 *
	 * @throws \Symfony\Component\HttpKernel\Exception\HttpException
	 */
	public function handle($request, Closure $next)
	{
		if ($this->app->isDownForMaintenance()) {
			throw new HttpException(503);
		}

		return $next($request);
	}
}
