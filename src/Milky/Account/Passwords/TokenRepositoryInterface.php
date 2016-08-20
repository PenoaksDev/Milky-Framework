<?php namespace Milky\Account\Passwords;

interface TokenRepositoryInterface
{
	/**
	 * Create a new token.
	 *
	 * @param  CanResetPassword $user
	 * @return string
	 */
	public function create( CanResetPassword $user );

	/**
	 * Determine if a token record exists and is valid.
	 *
	 * @param  CanResetPassword $user
	 * @param  string $token
	 * @return bool
	 */
	public function exists( CanResetPassword $user, $token );

	/**
	 * Delete a token record.
	 *
	 * @param  string $token
	 * @return void
	 */
	public function delete( $token );

	/**
	 * Delete expired tokens.
	 *
	 * @return void
	 */
	public function deleteExpired();
}
