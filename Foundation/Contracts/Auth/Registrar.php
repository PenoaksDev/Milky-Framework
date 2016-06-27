<?php

namespace Foundation\Contracts\Auth;

interface Registrar
{
	/**
	 * Get a validator for an incoming registration request.
	 *
	 * @param  array  $data
	 * @return \Foundation\Contracts\Validation\Validator
	 */
	public function validator(array $data);

	/**
	 * Create a new user instance after a valid registration.
	 *
	 * @param  array  $data
	 * @return \Foundation\Contracts\Auth\Authenticatable
	 */
	public function create(array $data);
}
