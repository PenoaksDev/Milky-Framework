<?php
namespace Penoaks\Http;

use Penoaks\Bindings\Bindings;
use Penoaks\Contracts\Validation\Factory as ValidationFactory;
use Penoaks\Contracts\Validation\ValidatesWhenResolved;
use Penoaks\Contracts\Validation\Validator;
use Penoaks\Http\Exception\HttpResponseException;
use Penoaks\Routing\Redirector;
use Penoaks\Validation\ValidatesWhenResolvedTrait;

class FormRequest extends Request implements ValidatesWhenResolved
{
	use ValidatesWhenResolvedTrait;

	/**
	 * The bindings instance.
	 *
	 * @var \Penoaks\Framework
	 */
	protected $bindings;

	/**
	 * The redirector instance.
	 *
	 * @var \Penoaks\Routing\Redirector
	 */
	protected $redirector;

	/**
	 * The URI to redirect to if validation fails.
	 *
	 * @var string
	 */
	protected $redirect;

	/**
	 * The route to redirect to if validation fails.
	 *
	 * @var string
	 */
	protected $redirectRoute;

	/**
	 * The controller action to redirect to if validation fails.
	 *
	 * @var string
	 */
	protected $redirectAction;

	/**
	 * The key to be used for the view error bag.
	 *
	 * @var string
	 */
	protected $errorBag = 'default';

	/**
	 * The input keys that should not be flashed on redirect.
	 *
	 * @var array
	 */
	protected $dontFlash = ['password', 'password_confirmation'];

	/**
	 * Get the validator instance for the request.
	 *
	 * @return \Penoaks\Contracts\Validation\Validator
	 */
	protected function getValidatorInstance()
	{
		$factory = $this->bindings->make( ValidationFactory::class );

		if ( method_exists( $this, 'validator' ) )
		{
			return $this->bindings->call( [$this, 'validator'], compact( 'factory' ) );
		}

		return $factory->make( $this->validationData(), $this->bindings->call( [
			$this,
			'rules'
		] ), $this->messages(), $this->attributes() );
	}

	/**
	 * Get data to be validated from the request.
	 *
	 * @return array
	 */
	protected function validationData()
	{
		return $this->all();
	}

	/**
	 * Handle a failed validation attempt.
	 *
	 * @param  \Penoaks\Contracts\Validation\Validator $validator
	 * @return void
	 *
	 * @throws \Penoaks\Http\Exception\HttpResponseException
	 */
	protected function failedValidation( Validator $validator )
	{
		throw new HttpResponseException( $this->response( $this->formatErrors( $validator ) ) );
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
			return $this->bindings->call( [$this, 'authorize'] );
		}

		return false;
	}

	/**
	 * Handle a failed authorization attempt.
	 *
	 * @return void
	 *
	 * @throws \Penoaks\Http\Exception\HttpResponseException
	 */
	protected function failedAuthorization()
	{
		throw new HttpResponseException( $this->forbiddenResponse() );
	}

	/**
	 * Get the proper failed validation response for the request.
	 *
	 * @param  array $errors
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function response( array $errors )
	{
		if ( ( $this->ajax() && !$this->pjax() ) || $this->wantsJson() )
		{
			return new JsonResponse( $errors, 422 );
		}

		return $this->redirector->to( $this->getRedirectUrl() )->withInput( $this->except( $this->dontFlash ) )->withErrors( $errors, $this->errorBag );
	}

	/**
	 * Get the response for a forbidden operation.
	 *
	 * @return \Penoaks\Http\Response
	 */
	public function forbiddenResponse()
	{
		return new Response( 'Forbidden', 403 );
	}

	/**
	 * Format the errors from the given Validator instance.
	 *
	 * @param  \Penoaks\Contracts\Validation\Validator $validator
	 * @return array
	 */
	protected function formatErrors( Validator $validator )
	{
		return $validator->getMessageBag()->toArray();
	}

	/**
	 * Get the URL to redirect to on a validation error.
	 *
	 * @return string
	 */
	protected function getRedirectUrl()
	{
		$url = $this->redirector->getUrlGenerator();

		if ( $this->redirect )
		{
			return $url->to( $this->redirect );
		}
		elseif ( $this->redirectRoute )
		{
			return $url->route( $this->redirectRoute );
		}
		elseif ( $this->redirectAction )
		{
			return $url->action( $this->redirectAction );
		}

		return $url->previous();
	}

	/**
	 * Set the Redirector instance.
	 *
	 * @param  \Penoaks\Routing\Redirector $redirector
	 * @return $this
	 */
	public function setRedirector( Redirector $redirector )
	{
		$this->redirector = $redirector;

		return $this;
	}

	/**
	 * Set the bindings implementation.
	 *
	 * @param  \Penoaks\Framework $bindings
	 * @return $this
	 */
	public function setBindings( Bindings $bindings )
	{
		$this->bindings = $bindings;

		return $this;
	}

	/**
	 * Get custom messages for validator errors.
	 *
	 * @return array
	 */
	public function messages()
	{
		return [];
	}

	/**
	 * Get custom attributes for validator errors.
	 *
	 * @return array
	 */
	public function attributes()
	{
		return [];
	}
}
