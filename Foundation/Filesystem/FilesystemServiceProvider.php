<?php

namespace Foundation\Filesystem;

use Foundation\Support\ServiceProvider;

class FilesystemServiceProvider extends ServiceProvider
{
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerNativeFilesystem();

		$this->registerFlysystem();
	}

	/**
	 * Register the native filesystem implementation.
	 *
	 * @return void
	 */
	protected function registerNativeFilesystem()
	{
		$this->fw->bindings->singleton('files', function ()
{
			return new Filesystem;
		});
	}

	/**
	 * Register the driver based filesystem.
	 *
	 * @return void
	 */
	protected function registerFlysystem()
	{
		$this->registerManager();

		$this->fw->bindings->singleton('filesystem.disk', function ()
{
			return $this->fw->bindings['filesystem']->disk($this->getDefaultDriver());
		});

		$this->fw->bindings->singleton('filesystem.cloud', function ()
{
			return $this->fw->bindings['filesystem']->disk($this->getCloudDriver());
		});
	}

	/**
	 * Register the filesystem manager.
	 *
	 * @return void
	 */
	protected function registerManager()
	{
		$this->fw->bindings->singleton('filesystem', function ()
{
			return new FilesystemManager($this->fw);
		});
	}

	/**
	 * Get the default file driver.
	 *
	 * @return string
	 */
	protected function getDefaultDriver()
	{
		return $this->fw->bindings['config']['filesystems.default'];
	}

	/**
	 * Get the default cloud based file driver.
	 *
	 * @return string
	 */
	protected function getCloudDriver()
	{
		return $this->fw->bindings['config']['filesystems.cloud'];
	}
}
