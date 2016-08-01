<?php namespace Milky\Http\Session;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface as BaseSessionInterface;

interface SessionInterface extends BaseSessionInterface
{
	/**
	 * Get the session handler instance.
	 *
	 * @return \SessionHandlerInterface
	 */
	public function getHandler();

	/**
	 * Determine if the session handler needs a request.
	 *
	 * @return bool
	 */
	public function handlerNeedsRequest();

	/**
	 * Set the request on the handler instance.
	 *
	 * @param  Request $request
	 */
	public function setRequestOnHandler( Request $request );

	/**
	 * Set the "previous" URL in the session.
	 *
	 * @param  string $url
	 */
	public function setPreviousUrl( $url );
}
