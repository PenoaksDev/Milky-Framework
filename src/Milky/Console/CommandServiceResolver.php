<?php namespace Milky\Console;

use Milky\Binding\Resolvers\ServiceResolver;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class CommandServiceResolver extends ServiceResolver
{
	// Vary simple, need to add more once the Console is fixed

	protected $commands = [];

	public function __set( $name, $value )
	{
		$this->commands[$name] = $value;
	}

	public function __get( $name )
	{
		return $this->commands[$name];
	}

	public function key()
	{
		return 'command';
	}
}
