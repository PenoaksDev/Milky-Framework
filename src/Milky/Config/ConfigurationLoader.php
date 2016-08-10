<?php namespace Milky\Config;

use Milky\Config\Builder\ConfigurationBuilder;
use Milky\Exceptions\FrameworkException;
use Milky\Framework;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class ConfigurationLoader
{
	/**
	 * @param Framework $fw
	 * @param Configuration $config
	 */
	public static function load( Framework $fw )
	{
		$items = [];

		// First we will see if we have a cache configuration file. If we do, we'll load
		// the configuration items from that file so that it is very quick. Otherwise
		// we will need to spin through every configuration file and load them all.
		if ( file_exists( $cached = $fw->buildPath( '__cache', 'config.php' ) ) )
		{
			$items = require $cached;
			$loadedFromCache = true;
		}

		$config = new Configuration( $items );

		// Next we will spin through all of the configuration files in the configuration
		// directory and load each one into the repository. This will make all of the
		// options available to the developer for use in various parts of this fw.
		if ( !isset( $loadedFromCache ) )
			static::loadConfigurationFiles( $fw, $config );

		date_default_timezone_set( $config->get( 'app.timezone', 'UTC' ) );

		mb_internal_encoding( 'UTF-8' );

		return $config;
	}

	/**
	 * Load the configuration items from all of the files.
	 *
	 * @param  Framework $fw
	 * @param  Configuration $config
	 */
	protected static function loadConfigurationFiles( Framework $fw, Configuration $config )
	{
		$configPath = $fw->buildPath( '__config' );
		foreach ( Finder::create()->files()->in( $configPath ) as $file )
		{
			$nesting = static::getConfigurationNesting( $file, $configPath );

			try
			{
				if ( ends_with( $file->getFilename(), '.yaml' ) )
					$config->set( $nesting . basename( $file->getRealPath(), '.yaml' ), Yaml::parse( file_get_contents( $file ) ) );

				if ( ends_with( $file->getFilename(), '.json' ) )
					$config->set( $nesting . basename( $file->getRealPath(), '.json' ), json_decode( file_get_contents( $file ) ) );

				if ( ends_with( $file->getFilename(), '.php' ) && is_array( $array = include( $file->getRealPath() ) ) )
					$config->set( $nesting . basename( $file->getRealPath(), '.php' ), $array );
			}
			catch ( \Throwable $e )
			{
				// TODO Pass configuration failures to the ExceptionHandler
				throw new \RuntimeException( "Failed to load configuration file [" . $file->getRealPath() . "]: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine() );
			}
		}
	}

	/**
	 * Get the configuration file nesting path.
	 *
	 * @param  SplFileInfo $file
	 * @param  string $configPath
	 * @return string
	 */
	protected static function getConfigurationNesting( SplFileInfo $file, $configPath )
	{
		$directory = dirname( $file->getRealPath() );

		if ( $tree = trim( str_replace( $configPath, '', $directory ), DIRECTORY_SEPARATOR ) )
			$tree = str_replace( DIRECTORY_SEPARATOR, '.', $tree ) . '.';

		return $tree;
	}

	/**
	 * Saves a builder to the filesystem
	 *
	 * @param ConfigurationBuilder $builder
	 *
	 * @throws FrameworkException
	 */
	public static function create( ConfigurationBuilder $builder  )
	{
		// TODO Currently only supports PHP files, need to add optional json and yaml support.

		$configPath = Framework::fw()->buildPath( '__config' );
		$configFile = $configPath . __ . $builder->key() . ".php";

		if ( file_put_contents( $configFile, $builder->toPhp( true ) ) === false || !is_array( $data = include( $configFile ) ) )
		{
			@unlink( $configFile );

			throw new FrameworkException( "Configuration error, either failed to write config file to [" . $configFile . "] or returned data is not an array." );
		}

		Framework::config()->set( $builder->key(), $data );
	}
}
