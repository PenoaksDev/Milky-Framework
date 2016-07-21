<?php namespace Milky\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class HttpResponseException extends HttpException
{
	/**
	 * The underlying response instance.
	 *
	 * @var Response
	 */
	protected $response;

	/**
	 * Create a new HTTP response exception instance.
	 *
	 * @param  Response $response
	 */
	public function __construct( Response $response )
	{
		$this->response = $response;
	}

	/**
	 * Get the underlying response instance.
	 *
	 * @return Response
	 */
	public function getResponse()
	{
		return $this->response;
	}
}
