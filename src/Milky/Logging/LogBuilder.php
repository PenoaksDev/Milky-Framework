<?php namespace Milky\Logging;

use Milky\Builders\Builder;
use Milky\Exceptions\FrameworkException;
use Milky\Facades\Config;
use Milky\Framework;
use Monolog\Logger as Monolog;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class LogBuilder
{
	/**
	 * Bootstrap the given application.
	 *
	 * @param  Framework $fw
	 */
	public static function build( Framework $fw )
	{
		$log = static::registerLogger( $fw );

		// If a custom Monolog configurator has been registered for the application
		// we will call that, passing Monolog along. Otherwise, we will grab the
		// the configurations for the log system and use it for configuration.
		if ( $fw->hasMonologConfigurator() )
			call_user_func( $fw->getMonologConfigurator(), $log->getMonolog() );
		else
			static::configureHandlers( $fw, Config::get( 'app.log' ), $log );

		return $log;
	}

	/**
	 * Register the logger instance in the bindings.
	 *
	 * @param  Framework $fw
	 * @return Logger
	 */
	protected function registerLogger( Framework $fw )
	{
		return new Logger( new Monolog( $fw->environment() ) );
	}

	/**
	 * Configure the Monolog handlers for the application.
	 *
	 * @param  string $handler
	 * @param  Logger $log
	 */
	protected function configureHandlers( Framework $fw, $handler, Logger $log )
	{
		switch ( strtolower( $handler ) )
		{
			case "single":
			{
				$log->useFiles( $fw->buildPath( '__logs', 'http.log' ), Config::get( 'app.log_level', 'debug' ) );
				break;
			}
			case "daily":
			{
				$maxFiles = Config::get( 'app.log_max_files' );
				$log->useDailyFiles( $fw->buildPath( '__logs', 'http.log' ), is_null( $maxFiles ) ? 5 : $maxFiles, Config::get( 'app.log_level', 'debug' ) );
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
				throw new FrameworkException( "The string [" . $handler . "] is not a valid log handler." );
		}
	}
}
