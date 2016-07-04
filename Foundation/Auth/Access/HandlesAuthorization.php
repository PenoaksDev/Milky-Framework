<?php

namespace Foundation\Auth\Access;

trait HandlesAuthorization
{
	/**
	 * Create a new access response.
	 *
	 * @param  string|null  $message
	 * @return \Foundation\Auth\Access\Response
	 */
	protected function allow($message = null)
	{
		return new Response($message);
	}

	/**
	 * Throws an unauthorized exception.
	 *
	 * @param  string  $message
	 * @return void
	 *
	 * @throws \Foundation\Auth\Access\AuthorizationException
	 */
	protected function deny($message = 'This action is unauthorized.')
	{
		throw new AuthorizationException($message);
	}
}
