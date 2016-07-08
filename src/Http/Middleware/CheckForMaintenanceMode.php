<?php

namesapce Penoaks\Http\Middleware;

use Closure;
use Foundation\Framework;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CheckForMaintenanceMode
{
	/**
	 * The application implementation.
	 *
	 * @var \Penoaks\Framework
	 */
	protected $fw;

	/**
	 * Create a new middleware instance.
	 *
	 * @param  \Penoaks\Framework  $fw
	 * @return void
	 */
	public function __construct(Framework $fw)
	{
		$this->fw = $fw;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Penoaks\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 *
	 * @throws \Symfony\Component\HttpKernel\Exception\HttpException
	 */
	public function handle($request, Closure $next)
	{
		if ($this->fw->isDownForMaintenance())
{
			throw new HttpException(503);
		}

		return $next($request);
	}
}
