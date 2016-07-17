<?php

namespace Penoaks\Bootstrap;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use Penoaks\Framework;
use Penoaks\Barebones\Bootstrap;

class DetectEnvironment implements Bootstrap
{
	/**
	 * Bootstrap the given application.
	 *
	 * @param  \Penoaks\Framework $fw
	 * @return void
	 */
	public function boot( Framework $fw )
	{
		if ( !$fw->configurationIsCached() )
		{
			$this->checkForSpecificEnvironmentFile( $fw );

			try
			{
				( new Dotenv( $fw->environmentPath(), $fw->environmentFile() ) )->load();
			}
			catch ( InvalidPathException $e )
			{
				//
			}
		}
	}

	/**
	 * Detect if a custom environment file matching the APP_ENV exists.
	 *
	 * @param  \Penoaks\Framework $fw
	 * @return void
	 */
	protected function checkForSpecificEnvironmentFile( $fw )
	{
		if ( !env( 'APP_ENV' ) )
			return;

		$file = $fw->environmentFile() . '.' . env( 'APP_ENV' );

		if ( file_exists( $fw->environmentPath() . '/' . $file ) )
			$fw->loadEnvironmentFrom( $file );
	}
}
