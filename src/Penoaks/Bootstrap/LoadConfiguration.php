<?php
namespace Penoaks\Bootstrap;

use Illuminate\Config\Repository;
use Penoaks\Barebones\Bootstrap;
use Penoaks\Framework;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;

class LoadConfiguration implements Bootstrap
{
	/**
	 * Bootstrap the given application.
	 *
	 * @param  Framework $fw
	 */
	public function boot( Framework $fw )
	{
		$items = [];

		// First we will see if we have a cache configuration file. If we do, we'll load
		// the configuration items from that file so that it is very quick. Otherwise
		// we will need to spin through every configuration file and load them all.
		if ( file_exists( $cached = $fw->buildPath( 'config.php', 'cache' ) ) )
		{
			$items = require $cached;
			$loadedFromCache = true;
		}

		$fw->bindings->instance( 'config', $config = new Repository( $items ) );

		// Next we will spin through all of the configuration files in the configuration
		// directory and load each one into the repository. This will make all of the
		// options available to the developer for use in various parts of this fw.
		if ( !isset( $loadedFromCache ) )
			$this->loadConfigurationFiles( $fw, $config );

		date_default_timezone_set( $config->get( 'app.timezone', 'UTC' ) );

		mb_internal_encoding( 'UTF-8' );
	}

	/**
	 * Load the configuration items from all of the files.
	 *
	 * @param  Framework $fw
	 * @param  Repository $config
	 */
	protected function loadConfigurationFiles( Framework $fw, Repository $config )
	{
		$configPath = $fw->buildPath( 'config', 'fw' );
		foreach ( Finder::create()->files()->in( $configPath ) as $file )
		{
			$nesting = $this->getConfigurationNesting( $file, $configPath );

			try
			{
				if ( ends_with( $file->getFilename(), '.yaml' ) )
					$config->set( $nesting . basename( $file->getRealPath(), '.yaml' ), Yaml::parse( file_get_contents( $file ) ) );

				if ( ends_with( $file->getFilename(), '.json' ) )
					$config->set( $nesting . basename( $file->getRealPath(), '.json' ), json_decode( file_get_contents( $file ) ) );

				if ( ends_with( $file->getFilename(), '.php' ) && is_array( $array = include_once( $file->getRealPath() ) ) )
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
	protected function getConfigurationNesting( SplFileInfo $file, $configPath )
	{
		$directory = dirname( $file->getRealPath() );

		if ( $tree = trim( str_replace( $configPath, '', $directory ), DIRECTORY_SEPARATOR ) )
			$tree = str_replace( DIRECTORY_SEPARATOR, '.', $tree ) . '.';

		return $tree;
	}
}
