<?php

namespace Penoaks\Validation;

use Penoaks\Contracts\Validation\ValidationException;
use Penoaks\Contracts\Validation\UnauthorizedException;

/**
 * Provides default implementation of ValidatesWhenResolved contract.
 */
trait ValidatesWhenResolvedTrait
{
	/**
	 * Validate the class instance.
	 *
	 * @return void
	 */
	public function validate()
	{
		$instance = $this->getValidatorInstance();

		if (! $this->passesAuthorization())
{
			$this->failedAuthorization();
		}
elseif (! $instance->passes())
{
			$this->failedValidation($instance);
		}
	}

	/**
	 * Get the validator instance for the request.
	 *
	 * @return \Penoaks\Validation\Validator
	 */
	protected function getValidatorInstance()
	{
		return $this->validator();
	}

	/**
	 * Handle a failed validation attempt.
	 *
	 * @param  \Penoaks\Validation\Validator  $validator
	 * @return void
	 *
	 * @throws \Penoaks\Contracts\Validation\ValidationException
	 */
	protected function failedValidation(Validator $validator)
	{
		throw new ValidationException($validator);
	}

	/**
	 * Determine if the request passes the authorization check.
	 *
	 * @return bool
	 */
	protected function passesAuthorization()
	{
		if (method_exists($this, 'authorize'))
{
			return $this->authorize();
		}

		return true;
	}

	/**
	 * Handle a failed authorization attempt.
	 *
	 * @return void
	 *
	 * @throws \Penoaks\Contracts\Validation\UnauthorizedException
	 */
	protected function failedAuthorization()
	{
		throw new UnauthorizedException;
	}
}
