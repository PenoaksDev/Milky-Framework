<?php

/*
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */

namespace Milky\Config\Builder;

use Milky\Helpers\Str;

class ConfigurationValue implements ConfigurationSection
{
	/**
	 * @var string
	 */
	protected $key;

	/**
	 * @var mixed
	 */
	protected $value;

	/**
	 * @var array
	 */
	protected $comments;

	/**
	 * @var string
	 */
	protected $commentTitle;

	/**
	 * ConfigurationValue constructor.
	 *
	 * @param $key
	 */
	public function __construct( $key, $value = null )
	{
		$this->key = $key;
		$this->value = $value;
	}

	public function withValue( $value )
	{
		$this->value = $value;
	}

	public function withComment( array $lines, $title = null )
	{
		$this->comments = $lines;
		$this->commentTitle = $title;
	}

	public function key()
	{
		return $this->key;
	}

	public function toArray()
	{
		return $this->value;
	}

	private function arrayToString( $array )
	{
		if ( count( $array ) == 0 )
			return "[]";

		$arr = "[\n";

		foreach ( $array as $k => $v )
			$arr .= Str::indent( "'" . $k . "' => '" . ( is_array( $v ) ? $this->arrayToString( $v ) : $v ) ) . "',\n";

		return $arr . "]";
	}

	public function toPhp()
	{
		if ( is_array( $this->value ) )
			return $this->arrayToString( $this->value );
		else
			return var_export( $this->value, true );
	}

	public function getPhpDoc()
	{
		$builder = "/*\n";

		if ( $this->commentTitle )
		{
			$builder .= " * --------------------------------------------------------------------------\n";
			$builder .= " * " . $this->commentTitle . "\n";
			$builder .= " * --------------------------------------------------------------------------\n";
		}

		foreach ( $this->comments as $comment )
			$builder .= " * " . $comment . "\n";

		$builder .= " * \n";
		$builder .= " * Default: " . Str::prependLines( $this->toPhp(), " *          " ) . "\n";

		return $builder . " */";
	}

	public function hasPhpDoc()
	{
		return count( $this->comments ) > 0;
	}
}
