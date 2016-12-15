<?php namespace Milky\Database\Eloquent;

/**
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
interface RoutableModel
{
	public function appendRoute( $route, &$parameters, &$appendedUrl );
}
