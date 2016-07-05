<?php

namespace Foundation\Bootstrap;

use Foundation\Config\Repository;
use Foundation\Contracts\Config\Repository as RepositoryContract;
use Foundation\Framework;
use Foundation\Interfaces\Bootstrap;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class LoadConfiguration implements Bootstrap
{
	/**
	 * Bootstrap the given application.
	 *
	 * @param  \Foundation\Framework  $fw
	 * @return void
	 */
	public function bootstrap(Framework $fw)
	{
		$items = [];

		// First we will see if we have a cache configuration file. If we do, we'll load
		// the configuration items from that file so that it is very quick. Otherwise
		// we will need to spin through every configuration file and load them all.
		if (file_exists($cached = $fw->getCachedConfigPath()))
		{
			$items = require $cached;
			$loadedFromCache = true;
		}

		$fw->bindings->instance('config', $config = new Repository($items));

		// Next we will spin through all of the configuration files in the configuration
		// directory and load each one into the repository. This will make all of the
		// options available to the developer for use in various parts of this fw.
		if (! isset($loadedFromCache))
		{
			$this->loadConfigurationFiles($fw, $config);
		}

		$fw->detectEnvironment(function () use ($config)
		{
			return $config->get('app.env', 'production');
		});

		date_default_timezone_set($config['app.timezone']);

		mb_internal_encoding('UTF-8');
	}

	/**
	 * Load the configuration items from all of the files.
	 *
	 * @param  \Foundation\Framework  $fw
	 * @param  \Foundation\Contracts\Config\Repository  $repository
	 * @return void
	 */
	protected function loadConfigurationFiles(Framework $fw, RepositoryContract $repository)
	{
		$configPath = realpath( $fw->configPath() );
		foreach ( Finder::create()->files()->in($configPath) as $file )
		{
			$nesting = $this->getConfigurationNesting( $file, $configPath );

			try
			{
				if ( Func::str_ends_with( $file->getFilename(), '.yaml' ) )
					$repository->set( $nesting . basename( $file->getRealPath(), '.yaml' ), Yaml::parse( file_get_contents( $file ) ) );

				if ( Func::str_ends_with( $file->getFilename(), '.json' ) )
					$repository->set( $nesting . basename( $file->getRealPath(), '.json' ), json_decode( file_get_contents( $file ) ) );

				if ( Func::str_ends_with( $file->getFilename(), '.php' ) && is_array( $array = require( $file->getRealPath() ) ) )
				{
					$repository->set( $nesting . basename( $file->getRealPath(), '.php' ), $array );
				}
			}
			catch ( \Exception $e )
			{
				throw new \RuntimeException( "Failed to load configuration file [" . $file->getRealPath() . "]: " . $e->getMessage() );
			}
		}
	}

	/**
	 * Get the configuration file nesting path.
	 *
	 * @param  \Symfony\Component\Finder\SplFileInfo  $file
	 * @param  string  $configPath
	 * @return string
	 */
	protected function getConfigurationNesting(SplFileInfo $file, $configPath)
	{
		$directory = dirname($file->getRealPath());

		if ($tree = trim(str_replace($configPath, '', $directory), DIRECTORY_SEPARATOR))
{
			$tree = str_replace(DIRECTORY_SEPARATOR, '.', $tree).'.';
		}

		return $tree;
	}
}
