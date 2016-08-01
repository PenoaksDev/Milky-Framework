<?php namespace Milky\Services;

use Milky\Exceptions\ServiceException;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
abstract class ServiceFactory
{
	/**
	 * Registered service managers
	 *
	 * @var ServiceFactory[]
	 */
	private static $managers = [];

	/**
	 * @return $this
	 */
	public static function i()
	{
		if ( array_key_exists( static::class, self::$managers ) )
			return self::$managers[static::class];

		$reflection = new \ReflectionClass( static::class );

		$constructor = $reflection->getConstructor();
		try
		{
			$builder = $reflection->getMethod( 'build' );
		}
		catch ( \ReflectionException $e )
		{
			$builder = null;
		}

		if ( !is_null( $builder ) && $builder->isStatic() )
		{
			$i = $builder->invoke( null );

			if ( !$i instanceof ServiceFactory )
				throw new ServiceException( "The build() method must return a new instance of service factory [" . static::class . "]" );
		}
		else if ( is_null( $constructor ) || $constructor->getNumberOfRequiredParameters() == 0 )
			$i = new static;
		else
			throw new ServiceException( "The service factory [" . static::class . "] must have a zero parameter constructor or implement 'public static build()'." );

		self::$managers[static::class] = $i;
		return $i;
	}

	public static function isInitialized()
	{
		return array_key_exists( static::class, static::$managers );
	}

	/**
	 * ServiceFactory constructor.
	 *
	 * @param $scaffold
	 */
	public function __construct()
	{
		if ( array_key_exists( static::class, self::$managers ) )
			throw new ServiceException( "The service factory [" . static::class . "] is already initialized, check your load order." );
		self::$managers[static::class] = $this;
	}
}
