<?php namespace Milky\Account\Permissions;

use Milky\Annotations\AnnotationReader;
use Milky\Annotations\CachedReader;
use Milky\Cache\CacheManager;
use Milky\Exceptions\Auth\PolicyException;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
abstract class Policy
{
	/*
	 * When implemented this class selectively overrides the internal permission checking of the framework.
	 * To implement, extend this [Policy] and create a protected array named nodes, e.g., protected $nodes = [];
	 *
	 * The nodes key will be the permission being defined and the value will be the method used to check the permission.
	 * The value must be callable (Closure, class@method, [class, method]) or a simple method string which must exist locally.
	 * e.g., 'articles.edit' => 'editArticles'
	 *
	 * The protected string $prefix will be automatically prefixed to the permission key.
	 */

	/**
	 * Defines the permission namespace prefix.
	 * Prefix will be prefixed to the namespace for the below methods.
	 *
	 * @var string
	 */
	protected $prefix = '';

	/**
	 * The permission nodes.
	 * Args available: $entity -> passed by arg name
	 * Permission => Callable Method
	 *
	 * @var array
	 */
	protected $nodes = [];

	/**
	 * Policy constructor.
	 */
	public function __construct()
	{
		if ( is_null( $this->prefix ) || !is_string( $this->prefix ) )
			throw new PolicyException( "The policy [" . static::class . "] prefix must be a string." );

		$reader = new AnnotationReader();

		$reader->addImports( ['\Milky\Account\Permissions\PermissionMethod'] );

		$reader = new CachedReader( $reader, CacheManager::i() );

		$class = new \ReflectionClass( static::class );

		foreach ( $class->getMethods() as $method )
		{
			if ( $anno = $reader->getMethodAnnotation( $method, PermissionMethod::class ) )
			{
				$namespace = ( empty( $this->prefix ) ? '' : $this->prefix . '.' ) . $anno->namespace;
				$this->nodes[$namespace] = [$this, $method->name];
			}
		}
	}

	/**
	 * @return string
	 */
	public function getPrefix()
	{
		return $this->prefix;
	}

	/**
	 * @return array
	 */
	public function getNodes()
	{
		return $this->nodes;
	}
}
