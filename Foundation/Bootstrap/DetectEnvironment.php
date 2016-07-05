<?php

namespace Foundation\Bootstrap;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use Foundation\Framework;
use Foundation\Interfaces\Bootstrap;

class DetectEnvironment implements Bootstrap
{
	/**
	 * Bootstrap the given application.
	 *
	 * @param  \Foundation\Framework $fw
	 * @return void
	 */
	public function bootstrap( Framework $fw )
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
	 * @param  \Foundation\Framework $fw
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
