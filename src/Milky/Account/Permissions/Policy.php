<?php namespace Milky\Account\Permissions;

use Milky\Annotations\AnnotationReader;
use Milky\Annotations\CachedReader;
use Milky\Cache\CacheManager;
use Milky\Exceptions\Auth\PolicyException;
use Milky\Facades\Log;

/**
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
abstract class Policy
{
	/*
	 * When implemented this class selectively supplements the internal permission checking of the framework.
	 * To implement, extend this [Policy] and create a protected array named prefix, e.g., 'protected $prefix = 'permission.prefix';'
	 *
	 * The prefix will be appended the each policy methods namespace. For each node you wish to supplement, add the annotation
	 * PolicyOptions to the phpdoc of each method, e.g., '@PolicyOptions( namespace="methodNode" )'.
	 *
	 * Define the desired arguments in your method, which will be injected by the framework's UniversalBuilder.
	 * Additionally arguments my be passed from the call to check the permission, e.g.,
	 * 'Permissions::check("permission.prefix.methodNode")' OR '@permission( "permission.prefix.methodNode" )' for blade templates.
	 *
	 * The method `before`
	 */

	/**
	 * Defines the permission namespace prefix.
	 * Prefix will be prefixed to the namespace for the below methods.
	 *
	 * @var string
	 */
	protected $prefix = '';

	/**
	 * The policy nodes.
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
			throw new PolicyException( "The policy [" . static::class . "] prefix must be a string, e.g., 'protected \$prefix = \"fw.prefix\";'" );

		$reader = new AnnotationReader();

		$reader->addImports( [PolicyOptions::class] );

		$reader = new CachedReader( $reader, CacheManager::i() );

		$class = new \ReflectionClass( static::class );

		foreach ( $class->getMethods() as $method )
			if ( $options = $reader->getMethodAnnotation( $method, PolicyOptions::class ) )
			{
				$namespace = ( empty( $this->prefix ) ? '' : $this->prefix . '.' ) . $options->namespace;
				$this->nodes[$namespace] = [$this, $method->name];
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
