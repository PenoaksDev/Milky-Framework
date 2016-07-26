<?php
namespace Penoaks\Http\Middleware;

use Closure;
use Milky\Http\Request;
use Penoaks\Framework;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CheckForMaintenanceMode
{
	/**
	 * The application implementation.
	 *
	 * @var Framework
	 */
	protected $fw;

	/**
	 * Create a new middleware instance.
	 *
	 * @param  Framework $fw
	 * @return void
	 */
	public function __construct( Framework $fw )
	{
		$this->fw = $fw;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  Request $request
	 * @param  Closure $next
	 * @return mixed
	 *
	 * @throws HttpException
	 */
	public function handle( $request, Closure $next )
	{
		if ( $this->fw->isDownForMaintenance() )
			throw new HttpException( 503 );

		return $next( $request );
	}
}
