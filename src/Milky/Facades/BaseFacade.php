<?php namespace Milky\Facades;

use Milky\Binding\BindingBuilder;
use Milky\Framework;

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
	 * @var mixed
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
	private static $facades = [];

	/**
	 * BaseFacade constructor.
	 *
	 * @param $scaffold
	 */
	public function __construct( $resolver = null )
	{
		self::$facades[static::class] = $this;

		if ( is_null( $resolver ) )
			$resolver = $this->__getResolver();
		$this->resolver = $resolver;
	}

	public static function __reset( $init = false )
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
			return self::$facades[static::class];
		$self = new static;
		if ( !array_key_exists( static::class, self::$facades ) )
			self::$facades[static::class] = $self;

		return $self;
	}

	public function __init()
	{
		$resolver = $this->resolver;
		if ( is_string( $resolver ) )
			$this->scaffold = Framework::fw()->$resolver;
		else if ( is_callable( $resolver ) )
			$this->scaffold = call_user_func( $resolver, $this );
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
			throw new \RuntimeException( "Something went wrong, the scaffolding is null." );

		if ( get_class( $self->scaffold ) == get_class( $self ) )
			throw new \RuntimeException( "Something went wrong, scaffolding is looping." );

		try
		{
			// return call_user_func_array( [$self->scaffold, $method], $args );

			/**
			 * We use reflections to invoke the method so we can exclusively catch the ReflectionException if something goes wrong.
			 */
			$reflection = new \ReflectionMethod( get_class( $self->scaffold ), $method );
			return $reflection->invokeArgs( $self->scaffold, $args );
		}
		catch ( \ReflectionException $e )
		{
			if ( method_exists( $self->scaffold, '__call' ) )
				return $self->scaffold->__call( $method, $args );
			throw new \RuntimeException( "Failed to call dynamic method [" . $method . "] from scaffold class [" . get_class( $self->scaffold ) . "], does it exist?" );
		}
	}
}
