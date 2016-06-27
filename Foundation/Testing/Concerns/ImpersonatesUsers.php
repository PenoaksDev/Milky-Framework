<?php

namespace Foundation\Testing\Concerns;

use Foundation\Contracts\Auth\Authenticatable as UserContract;

trait ImpersonatesUsers
{
	/**
	 * Set the currently logged in user for the application.
	 *
	 * @param  \Foundation\Contracts\Auth\Authenticatable  $user
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
	 * @param  \Foundation\Contracts\Auth\Authenticatable  $user
	 * @param  string|null  $driver
	 * @return void
	 */
	public function be(UserContract $user, $driver = null)
	{
		$this->app['auth']->guard($driver)->setUser($user);
	}
}
