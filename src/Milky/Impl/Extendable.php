<?php namespace Milky\Impl;

use Milky\Binding\UniversalBuilder;
use Milky\Exceptions\FrameworkException;

/**
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
trait Extendable
{
	private static $extended = null;

	public static function extend( $class )
	{
		if ( !is_subclass_of( $class, static::class, true ) )
			throw new FrameworkException( "Class [" . $class . "] must extend [" . static::class . "]" );

		static::$extended = $class;
	}

	public static function build( array $parameters = [] )
	{
		if ( is_null( static::$extended ) )
			return UniversalBuilder::buildClass( static::class, $parameters );
		else
			return UniversalBuilder::buildClass( static::$extended, $parameters );
	}
}
