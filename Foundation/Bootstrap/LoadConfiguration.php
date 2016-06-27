<?php

namespace Foundation\Bootstrap;

use Foundation\Config\Repository;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Foundation\Contracts\Foundation\Application;
use Foundation\Contracts\Config\Repository as RepositoryContract;

class LoadConfiguration
{
	/**
	 * Bootstrap the given application.
	 *
	 * @param  \Foundation\Contracts\Foundation\Application  $app
	 * @return void
	 */
	public function bootstrap(Application $app)
	{
		$items = [];

		// First we will see if we have a cache configuration file. If we do, we'll load
		// the configuration items from that file so that it is very quick. Otherwise
		// we will need to spin through every configuration file and load them all.
		if (file_exists($cached = $app->getCachedConfigPath())) {
			$items = require $cached;

			$loadedFromCache = true;
		}

		$app->instance('config', $config = new Repository($items));

		// Next we will spin through all of the configuration files in the configuration
		// directory and load each one into the repository. This will make all of the
		// options available to the developer for use in various parts of this app.
		if (! isset($loadedFromCache)) {
			$this->loadConfigurationFiles($app, $config);
		}

		$app->detectEnvironment(function () use ($config) {
			return $config->get('app.env', 'production');
		});

		date_default_timezone_set($config['app.timezone']);

		mb_internal_encoding('UTF-8');
	}

	/**
	 * Load the configuration items from all of the files.
	 *
	 * @param  \Foundation\Contracts\Foundation\Application  $app
	 * @param  \Foundation\Contracts\Config\Repository  $repository
	 * @return void
	 */
	protected function loadConfigurationFiles(Application $app, RepositoryContract $repository)
	{
		foreach ($this->getConfigurationFiles($app) as $key => $path) {
			$repository->set($key, require $path);
		}
	}

	/**
	 * Get all of the configuration files for the application.
	 *
	 * @param  \Foundation\Contracts\Foundation\Application  $app
	 * @return array
	 */
	protected function getConfigurationFiles(Application $app)
	{
		$files = [];

		$configPath = realpath($app->configPath());

		foreach (Finder::create()->files()->name('*.php')->in($configPath) as $file) {
			$nesting = $this->getConfigurationNesting($file, $configPath);

			$files[$nesting.basename($file->getRealPath(), '.php')] = $file->getRealPath();
		}

		return $files;
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

		if ($tree = trim(str_replace($configPath, '', $directory), DIRECTORY_SEPARATOR)) {
			$tree = str_replace(DIRECTORY_SEPARATOR, '.', $tree).'.';
		}

		return $tree;
	}
}
