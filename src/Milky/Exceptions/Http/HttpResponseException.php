<?php namespace Milky\Exceptions\Http;

use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class HttpResponseException extends RuntimeException
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
	 * @return void
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
