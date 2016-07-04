<?php

namespace Foundation\Validation;

use Foundation\Contracts\Validation\ValidationException;
use Foundation\Contracts\Validation\UnauthorizedException;

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
	 * @return \Foundation\Validation\Validator
	 */
	protected function getValidatorInstance()
	{
		return $this->validator();
	}

	/**
	 * Handle a failed validation attempt.
	 *
	 * @param  \Foundation\Validation\Validator  $validator
	 * @return void
	 *
	 * @throws \Foundation\Contracts\Validation\ValidationException
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
	 * @throws \Foundation\Contracts\Validation\UnauthorizedException
	 */
	protected function failedAuthorization()
	{
		throw new UnauthorizedException;
	}
}
