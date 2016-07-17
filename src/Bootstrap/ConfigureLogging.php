<?php
namespace Penoaks\Bootstrap;

use Monolog\Logger as Monolog;
use Penoaks\Barebones\Bootstrap;
use Penoaks\Config\Config;
use Penoaks\Framework;
use Penoaks\Framework\Env;
use Penoaks\Logging\Log;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class ConfigureLogging implements Bootstrap
{
	/**
	 * Bootstrap the given application.
	 *
	 * @param  Framework $fw
	 */
	public function boot( Framework $fw )
	{
		$log = $this->registerLogger( $fw );

		// If a custom Monolog configurator has been registered for the application
		// we will call that, passing Monolog along. Otherwise, we will grab the
		// the configurations for the log system and use it for configuration.
		if ( $fw->hasMonologConfigurator() )
			call_user_func( $fw->getMonologConfigurator(), $log->getMonolog() );
		else
			$this->configureHandlers( Config::get('app.log'), $log );
	}

	/**
	 * Register the logger instance in the bindings.
	 *
	 * @param  Framework $fw
	 * @return Log
	 */
	protected function registerLogger( Framework $fw )
	{
		$fw->bindings->instance( 'log', $log = new Log( new Monolog( $fw->environment() ), $fw->bindings['events'] ) );
		return $log;
	}

	/**
	 * Configure the Monolog handlers for the application.
	 *
	 * @param  string $handler
	 * @param  Log $log
	 */
	protected function configureHandlers( $handler, Log $log )
	{
		switch ( strtolower( $handler ) )
		{
			case "single":
			{
				$log->useFiles( Env::get( 'path.storage' ) . __ . 'logs/default.log', Config::get( 'app.log_level', 'debug' ) );
				break;
			}
			case "daily":
			{
				$maxFiles = Config::get( 'app.log_max_files' );
				$log->useDailyFiles( Env::get( 'path.storage' ) . __ . 'logs/default.log', is_null( $maxFiles ) ? 5 : $maxFiles, Config::get( 'app.log_level', 'debug' ) );
				break;
			}
			case "syslog":
			{
				$log->useSyslog( 'framework', Config::get( 'app.log_level', 'debug' ) );
				break;
			}
			case "errorlog":
			{
				$log->useErrorLog( Config::get( 'app.log_level', 'debug' ) );
				break;
			}
			default:
			{
				throw new \RuntimeException( $handler . " is not a valid log handler." );
			}
		}
	}
}
