<?php namespace Milky\Validation;

use Milky\Exceptions\Validation\UnauthorizedException;
use Milky\Exceptions\Validation\ValidationException;

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

		if ( !$this->passesAuthorization() )
		{
			$this->failedAuthorization();
		}
		elseif ( !$instance->passes() )
		{
			$this->failedValidation( $instance );
		}
	}

	/**
	 * Get the validator instance for the request.
	 *
	 * @return \Milky\Validation\Validator
	 */
	protected function getValidatorInstance()
	{
		return $this->validator();
	}

	/**
	 * Handle a failed validation attempt.
	 *
	 * @param  \Milky\Validation\Validator $validator
	 * @return void
	 *
	 * @throws \Illuminate\Contracts\Validation\ValidationException
	 */
	protected function failedValidation( Validator $validator )
	{
		throw new ValidationException( $validator );
	}

	/**
	 * Determine if the request passes the authorization check.
	 *
	 * @return bool
	 */
	protected function passesAuthorization()
	{
		if ( method_exists( $this, 'authorize' ) )
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
	 * @throws \Illuminate\Contracts\Validation\UnauthorizedException
	 */
	protected function failedAuthorization()
	{
		throw new UnauthorizedException;
	}
}
