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

class ConfigurationBuilder implements ConfigurationSection
{
	/**
	 * @var string
	 */
	protected $key;

	/**
	 * @var ConfigurationSection[]
	 */
	protected $sections;

	/**
	 * @var array
	 */
	protected $comments;

	/**
	 * @var string
	 */
	protected $commentTitle;

	/**
	 * ConfigurationBuilder constructor.
	 *
	 * @param string $key
	 */
	public function __construct( $key )
	{
		$this->key = $key;
	}

	public function withComment( array $lines, $title = null )
	{
		$this->comments = $lines;
		$this->commentTitle = $title;
	}

	/**
	 * @param string $key
	 * @return ConfigurationBuilder
	 */
	public function group( $key )
	{
		return $this->sections[$key] = new ConfigurationBuilder( $key );
	}

	/**
	 * @param string $key
	 * @return ConfigurationValue
	 */
	public function add( $key, $value )
	{
		return $this->sections[$key] = new ConfigurationValue( $key, $value );
	}

	public function toPhp( $includeOpeningTag = false )
	{
		$builder = "";

		if ( $includeOpeningTag )
		{
			$builder = "<?php\n\n";

			if ( $this->hasPhpDoc() )
				$builder .= Str::indent( $this->getPhpDoc() ) . "\n";

			$builder .= "return ";
		}

		$builder .= "[\n\n";

		foreach ( $this->sections as $section )
		{
			if ( $section->hasPhpDoc() )
				$builder .= Str::indent( $section->getPhpDoc() ) . "\n";
			$builder .= Str::indent( "'" . $section->key() . "' => " . $section->toPhp() ) . ",\n\n";
		}

		$builder .= "]";

		if ( $includeOpeningTag )
			$builder .= ";\n";

		return $builder;
	}

	public function toArray()
	{
		$arr =  [];

		foreach ( $this->sections as $section )
			$arr[ $section->key() ] = $section->toArray();

		return $arr;
	}

	public function key()
	{
		return $this->key;
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
