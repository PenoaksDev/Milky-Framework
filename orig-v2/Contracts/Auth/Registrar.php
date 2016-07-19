<?php

namespace Penoaks\Contracts\Auth;

interface Registrar
{
	/**
	 * Get a validator for an incoming registration request.
	 *
	 * @param  array  $data
	 * @return \Penoaks\Contracts\Validation\Validator
	 */
	public function validator(array $data);

	/**
	 * Create a new user instance after a valid registration.
	 *
	 * @param  array  $data
	 * @return \Penoaks\Contracts\Auth\Authenticatable
	 */
	public function create(array $data);
}
