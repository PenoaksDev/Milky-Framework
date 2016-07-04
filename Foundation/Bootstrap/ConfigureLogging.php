<?php

namespace Foundation\Bootstrap;

use Foundation\Log\Writer;
use Monolog\Logger as Monolog;
use Foundation\Framework;

class ConfigureLogging
{
	/**
	 * Bootstrap the given application.
	 *
	 * @param  \Foundation\Framework  $fw
	 * @return void
	 */
	public function bootstrap(Framework $fw)
	{
		$log = $this->registerLogger($fw);

		// If a custom Monolog configurator has been registered for the application
		// we will call that, passing Monolog along. Otherwise, we will grab the
		// the configurations for the log system and use it for configuration.
		if ($fw->hasMonologConfigurator())
		{
			call_user_func( $fw->getMonologConfigurator(), $log->getMonolog() );
		}
		else
		{
			$this->configureHandlers($fw, $log);
		}
	}

	/**
	 * Register the logger instance in the bindings.
	 *
	 * @param  \Foundation\Framework  $fw
	 * @return \Foundation\Log\Writer
	 */
	protected function registerLogger(Framework $fw)
	{
		$fw->bindings->instance('log', $log = new Writer(
			new Monolog($fw->environment()), $fw->bindings['events'])
		);

		return $log;
	}

	/**
	 * Configure the Monolog handlers for the application.
	 *
	 * @param  \Foundation\Framework  $fw
	 * @param  \Foundation\Log\Writer  $log
	 * @return void
	 */
	protected function configureHandlers(Framework $fw, Writer $log)
	{
		$method = 'configure'.ucfirst($fw->bindings['config']['app.log']).'Handler';

		$this->{$method}($fw, $log);
	}

	/**
	 * Configure the Monolog handlers for the application.
	 *
	 * @param  \Foundation\Framework  $fw
	 * @param  \Foundation\Log\Writer  $log
	 * @return void
	 */
	protected function configureSingleHandler(Framework $fw, Writer $log)
	{
		$log->useFiles(
			$fw->storagePath().'/logs/framework.log',
			$fw->make('config')->get('app.log_level', 'debug')
		);
	}

	/**
	 * Configure the Monolog handlers for the application.
	 *
	 * @param  \Foundation\Framework  $fw
	 * @param  \Foundation\Log\Writer  $log
	 * @return void
	 */
	protected function configureDailyHandler(Framework $fw, Writer $log)
	{
		$config = $fw->make('config');

		$maxFiles = $config->get('app.log_max_files');

		$log->useDailyFiles(
			$fw->storagePath().'/logs/framework.log', is_null($maxFiles) ? 5 : $maxFiles,
			$config->get('app.log_level', 'debug')
		);
	}

	/**
	 * Configure the Monolog handlers for the application.
	 *
	 * @param  \Foundation\Framework  $fw
	 * @param  \Foundation\Log\Writer  $log
	 * @return void
	 */
	protected function configureSyslogHandler(Framework $fw, Writer $log)
	{
		$log->useSyslog(
			'framework',
			$fw->make('config')->get('app.log_level', 'debug')
		);
	}

	/**
	 * Configure the Monolog handlers for the application.
	 *
	 * @param  \Foundation\Framework  $fw
	 * @param  \Foundation\Log\Writer  $log
	 * @return void
	 */
	protected function configureErrorlogHandler(Framework $fw, Writer $log)
	{
		$log->useErrorLog($fw->make('config')->get('app.log_level', 'debug'));
	}
}
