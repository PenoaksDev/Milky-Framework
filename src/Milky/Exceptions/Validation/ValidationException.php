<?php namespace Milky\Exceptions\Validation;

use Milky\Http\Response;
use Milky\Validation\Validator;

class ValidationException extends \Exception
{
	/**
	 * The validator instance.
	 *
	 * @var Validator
	 */
	public $validator;
	/**
	 * The recommended response to send to the client.
	 *
	 * @var Response|null
	 */
	public $response;
	/**
	 * Create a new exception instance.
	 *
	 * @param  Validator  $validator
	 * @param  Response  $response
	 * @return void
	 */
	public function __construct($validator, $response = null)
	{
		parent::__construct('The given data failed to pass validation.');
		$this->response = $response;
		$this->validator = $validator;
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
