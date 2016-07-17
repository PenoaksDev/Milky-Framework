<?php
namespace Penoaks\Facades;

use Penoaks\Bindings\Bindings;
use Psy\Exception\RuntimeException;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
abstract class BaseFacade
{
	/**
	 * @var \stdClass
	 */
	protected $scaffold;

	/**
	 * @var mixed
	 */
	protected $resolver;

	/**
	 * Array of facades,
	 * Populated as used.
	 *
	 * @var array
	 */
	private static $facades = array();

	/**
	 * BaseFacade constructor.
	 *
	 * @param $scaffold
	 */
	public function __construct( $resolver = null )
	{
		self::$facades[ static::class ] = $this;

		if ( is_null( $resolver ) )
			$resolver = $this->__getResolver();
		$this->resolver = $resolver;
	}

	public static function __reset( bool $init = false )
	{
		$self = static::__self();
		$self->scaffold = null;
		if ( $init )
			$self->__init();
	}

	/**
	 * @return BaseFacade
	 */
	public static function __self()
	{
		if ( array_key_exists( static::class, self::$facades ) )
			return self::$facades[ static::class ];
		$self = Bindings::get( static::class );
		if ( !array_key_exists( static::class, self::$facades ) )
			self::$facades[ static::class ] = $self;
		return $self;
	}

	public function __init()
	{
		$resolver = $this->resolver;
		if ( is_string( $resolver ) )
			$this->scaffold = Bindings::get( $resolver );
		else if ( is_callable( $resolver ) )
			$this->scaffold = Bindings::i()->call( $resolver );
		else
			$this->scaffold = $resolver;
	}

	protected abstract function __getResolver();

	public static function __callStatic( $method, $args )
	{
		self::__do( $method, $args );
		Log::warning( "Static method [" . $method . "] is not implemented in facade class [" . static::class . "]" );
	}

	protected static function __do( $method, $args )
	{
		$self = static::__self();

		if ( is_null( $self->scaffold ) )
			$self->__init();

		if ( is_null( $self->scaffold ) )
			throw new RuntimeException( "Something went wrong, the scaffolding is null." );

		try
		{
			Bindings::i()->call( [
				$self->scaffold,
				$method
			], $args );
		}
		catch ( \RuntimeException $e )
		{
			throw new RuntimeException( "Dynamic method [" . $method . "] is missing from scaffold class [" . get_class( self::$self->scaffold ) . "]" );
		}
	}
}
