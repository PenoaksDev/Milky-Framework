<?php namespace Milky\Auth\Access;

use Milky\Account\Types\Account;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
trait AuthorizesRequests
{
	/**
	 * Authorize a given action against a set of arguments.
	 *
	 * @param  mixed $ability
	 * @param  mixed|array $arguments
	 * @return Response
	 *
	 * @throws AuthorizationException
	 */
	public function authorize( $ability, $arguments = [] )
	{
		list( $ability, $arguments ) = $this->parseAbilityAndArguments( $ability, $arguments );

		return app( Gate::class )->authorize( $ability, $arguments );
	}

	/**
	 * Authorize a given action for a user.
	 *
	 * @param  Account|mixed $user
	 * @param  mixed $ability
	 * @param  mixed|array $arguments
	 * @return Response
	 *
	 * @throws AuthorizationException
	 */
	public function authorizeForUser( $user, $ability, $arguments = [] )
	{
		list( $ability, $arguments ) = $this->parseAbilityAndArguments( $ability, $arguments );

		return app( Gate::class )->forUser( $user )->authorize( $ability, $arguments );
	}

	/**
	 * Guesses the ability's name if it wasn't provided.
	 *
	 * @param  mixed $ability
	 * @param  mixed|array $arguments
	 * @return array
	 */
	protected function parseAbilityAndArguments( $ability, $arguments )
	{
		if ( is_string( $ability ) )
		{
			return [$ability, $arguments];
		}

		return [debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 3 )[2]['function'], $ability];
	}
}
