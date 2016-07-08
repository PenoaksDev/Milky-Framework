<?php
namespace Penoaks\Bootstrap;

use Monolog\Logger as Monolog;
use Penoaks\Barebones\Bootstrap;
use Penoaks\Bindings\Bindings;
use Penoaks\Framework;
use Penoaks\Framework\Env;
use Penoaks\Log\Writer;

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
	public function bootstrap( Framework $fw )
	{
		$log = $this->registerLogger( $fw );

		// If a custom Monolog configurator has been registered for the application
		// we will call that, passing Monolog along. Otherwise, we will grab the
		// the configurations for the log system and use it for configuration.
		if ( $fw->hasMonologConfigurator() )
			call_user_func( $fw->getMonologConfigurator(), $log->getMonolog() );
		else
			$this->configureHandlers( $fw, $log );
	}

	/**
	 * Register the logger instance in the bindings.
	 *
	 * @param  Framework $fw
	 * @return Writer
	 */
	protected function registerLogger( Framework $fw )
	{
		$fw->bindings->instance( 'log', $log = new Writer( new Monolog( $fw->environment() ), $fw->bindings['events'] ) );
		return $log;
	}

	/**
	 * Configure the Monolog handlers for the application.
	 *
	 * @param  Framework $fw
	 * @param  Writer $log
	 */
	protected function configureHandlers( Framework $fw, Writer $log )
	{
		$method = 'configure' . ucfirst( $fw->bindings['config']['app.log'] ) . 'Handler';
		$this->{$method}( $fw->bindings, $log );
	}

	/**
	 * Configure the Monolog handlers for the application.
	 *
	 * @param  Bindings $fw
	 * @param  Writer $log
	 * @return void
	 */
	protected function configureSingleHandler( Bindings $bindings, Writer $log )
	{
		$log->useFiles( Env::get( 'path.storage' ) . __ . 'logs/default.log', $bindings->make( 'config' )->get( 'app.log_level', 'debug' ) );
	}

	/**
	 * Configure the Monolog handlers for the application.
	 *
	 * @param  Bindings $fw
	 * @param  Writer $log
	 * @return void
	 */
	protected function configureDailyHandler( Bindings $bindings, Writer $log )
	{
		$config = $bindings->get( 'config' );
		$maxFiles = $config->get( 'app.log_max_files' );
		$log->useDailyFiles( Env::get( 'path.storage' ) . __ . 'logs/default.log', is_null( $maxFiles ) ? 5 : $maxFiles, $config->get( 'app.log_level', 'debug' ) );
	}

	/**
	 * Configure the Monolog handlers for the application.
	 *
	 * @param  Bindings $fw
	 * @param  Writer $log
	 * @return void
	 */
	protected function configureSyslogHandler( Bindings $bindings, Writer $log )
	{
		$log->useSyslog( 'framework', $bindings->make( 'config' )->get( 'app.log_level', 'debug' ) );
	}

	/**
	 * Configure the Monolog handlers for the application.
	 *
	 * @param  Bindings $fw
	 * @param  Writer $log
	 * @return void
	 */
	protected function configureErrorlogHandler( Bindings $bindings, Writer $log )
	{
		$log->useErrorLog( $bindings->make( 'config' )->get( 'app.log_level', 'debug' ) );
	}
}
