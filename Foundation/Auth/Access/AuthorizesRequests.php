<?php

namespace Foundation\Auth\Access;

use Foundation\Contracts\Auth\Access\Gate;

trait AuthorizesRequests
{
	/**
	 * Authorize a given action against a set of arguments.
	 *
	 * @param  mixed  $ability
	 * @param  mixed|array  $arguments
	 * @return \Foundation\Auth\Access\Response
	 *
	 * @throws \Foundation\Auth\Access\AuthorizationException
	 */
	public function authorize($ability, $arguments = [])
	{
		list($ability, $arguments) = $this->parseAbilityAndArguments($ability, $arguments);

		return fw(Gate::class)->authorize($ability, $arguments);
	}

	/**
	 * Authorize a given action for a user.
	 *
	 * @param  \Foundation\Contracts\Auth\Authenticatable|mixed  $user
	 * @param  mixed  $ability
	 * @param  mixed|array  $arguments
	 * @return \Foundation\Auth\Access\Response
	 *
	 * @throws \Foundation\Auth\Access\AuthorizationException
	 */
	public function authorizeForUser($user, $ability, $arguments = [])
	{
		list($ability, $arguments) = $this->parseAbilityAndArguments($ability, $arguments);

		return fw(Gate::class)->forUser($user)->authorize($ability, $arguments);
	}

	/**
	 * Guesses the ability's name if it wasn't provided.
	 *
	 * @param  mixed  $ability
	 * @param  mixed|array  $arguments
	 * @return array
	 */
	protected function parseAbilityAndArguments($ability, $arguments)
	{
		if (is_string($ability))
{
			return [$ability, $arguments];
		}

		return [debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2]['function'], $ability];
	}
}
