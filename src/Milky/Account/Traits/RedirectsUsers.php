<?php namespace Milky\Account\Traits;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
trait RedirectsUsers
{
	/**
	 * Get the post register / login redirect path.
	 *
	 * @return string
	 */
	public function redirectPath()
	{
		if ( property_exists( $this, 'redirectPath' ) )
			return $this->redirectPath;

		return property_exists( $this, 'redirectTo' ) ? $this->redirectTo : '/home';
	}
}
