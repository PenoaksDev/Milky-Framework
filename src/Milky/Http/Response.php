<?php namespace Milky\Http;

use ArrayObject;
use Exception;
use JsonSerializable;
use Milky\Exceptions\Http\HttpResponseException;
use Milky\Impl\Jsonable;
use Milky\Impl\Renderable;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

/**
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class Response extends BaseResponse
{
	/**
	 * The original content of the response.
	 *
	 * @var mixed
	 */
	public $original;

	/**
	 * The exception that triggered the error response (if applicable).
	 *
	 * @var \Exception
	 */
	public $exception;

	/**
	 * Set the content on the response.
	 *
	 * @param  mixed $content
	 * @return $this
	 */
	public function setContent( $content )
	{
		$this->original = $content;

		// If the content is "JSONable" we will set the appropriate header and convert
		// the content to JSON. This is useful when returning something like models
		// from routes that will be automatically transformed to their JSON form.
		if ( $this->shouldBeJson( $content ) )
		{
			$this->header( 'Content-Type', 'application/json' );
			$content = $this->morphToJson( $content );
		}

		// If this content implements the "Renderable" interface then we will call the
		// render method on the object so we will avoid any "__toString" exceptions
		// that might be thrown and have their errors obscured by PHP's handling.
		elseif ( $content instanceof Renderable )
			$content = $content->render();

		return parent::setContent( $content );
	}

	/**
	 * Morph the given content into JSON.
	 *
	 * @param  mixed $content
	 * @return string
	 */
	protected function morphToJson( $content )
	{
		if ( $content instanceof Jsonable )
		{
			return $content->toJson();
		}

		return json_encode( $content );
	}

	/**
	 * Determine if the given content should be turned into JSON.
	 *
	 * @param  mixed $content
	 * @return bool
	 */
	protected function shouldBeJson( $content )
	{
		return $content instanceof Jsonable || $content instanceof ArrayObject || $content instanceof JsonSerializable || is_array( $content );
	}

	/**
	 * Get the original response content.
	 *
	 * @return mixed
	 */
	public function getOriginalContent()
	{
		return $this->original;
	}

	/**
	 * Set the exception to attach to the response.
	 *
	 * @param  \Exception $e
	 * @return $this
	 */
	public function withException( Exception $e )
	{
		$this->exception = $e;

		return $this;
	}

	/**
	 * Get the status code for the response.
	 *
	 * @return int
	 */
	public function status()
	{
		return $this->getStatusCode();
	}

	/**
	 * Get the content of the response.
	 *
	 * @return string
	 */
	public function content()
	{
		return $this->getContent();
	}

	/**
	 * Set a header on the Response.
	 *
	 * @param  string $key
	 * @param  string $value
	 * @param  bool $replace
	 * @return $this
	 */
	public function header( $key, $value, $replace = true )
	{
		$this->headers->set( $key, $value, $replace );

		return $this;
	}

	/**
	 * Add an array of headers to the response.
	 *
	 * @param  array $headers
	 * @return $this
	 */
	public function withHeaders( array $headers )
	{
		foreach ( $headers as $key => $value )
			$this->headers->set( $key, $value );

		return $this;
	}

	/**
	 * Add a cookie to the response.
	 *
	 * @param  Cookie $cookie
	 * @return $this
	 */
	public function withCookie( Cookie $cookie )
	{
		$this->headers->setCookie( $cookie );

		return $this;
	}

	/**
	 * Throws the response in a HttpResponseException instance.
	 *
	 * @throws HttpResponseException;
	 */
	public function throwResponse()
	{
		throw new HttpResponseException( $this );
	}
}
