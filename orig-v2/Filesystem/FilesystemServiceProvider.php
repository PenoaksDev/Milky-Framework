<?php
namespace Penoaks\Filesystem;

use Penoaks\Barebones\ServiceProvider;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
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
		$this->bindings->singleton( 'files', function ()
		{
			return new Filesystem;
		} );
	}

	/**
	 * Register the driver based filesystem.
	 *
	 * @return void
	 */
	protected function registerFlysystem()
	{
		$this->registerManager();

		$this->bindings->singleton( 'filesystem.disk', function ()
		{
			return $this->bindings['filesystem']->disk( $this->getDefaultDriver() );
		} );

		$this->bindings->singleton( 'filesystem.cloud', function ()
		{
			return $this->bindings['filesystem']->disk( $this->getCloudDriver() );
		} );
	}

	/**
	 * Register the filesystem manager.
	 *
	 * @return void
	 */
	protected function registerManager()
	{
		$this->bindings->singleton( 'filesystem', function ()
		{
			return new FilesystemManager( $this->fw );
		} );
	}

	/**
	 * Get the default file driver.
	 *
	 * @return string
	 */
	protected function getDefaultDriver()
	{
		return $this->bindings['config']['filesystems.default'];
	}

	/**
	 * Get the default cloud based file driver.
	 *
	 * @return string
	 */
	protected function getCloudDriver()
	{
		return $this->bindings['config']['filesystems.cloud'];
	}
}
