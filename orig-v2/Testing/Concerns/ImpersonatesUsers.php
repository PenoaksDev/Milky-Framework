<?php

namespace Penoaks\Testing\Concerns;

use Penoaks\Contracts\Auth\Authenticatable as UserContract;

trait ImpersonatesUsers
{
	/**
	 * Set the currently logged in user for the application.
	 *
	 * @param  \Penoaks\Contracts\Auth\Authenticatable  $user
	 * @param  string|null  $driver
	 * @return $this
	 */
	public function actingAs(UserContract $user, $driver = null)
	{
		$this->be($user, $driver);

		return $this;
	}

	/**
	 * Set the currently logged in user for the application.
	 *
	 * @param  \Penoaks\Contracts\Auth\Authenticatable  $user
	 * @param  string|null  $driver
	 * @return void
	 */
	public function be(UserContract $user, $driver = null)
	{
		$this->fw->bindings['auth']->guard($driver)->setUser($user);
	}
}
