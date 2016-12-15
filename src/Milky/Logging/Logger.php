<?php namespace Milky\Logging;

use Closure;
use InvalidArgumentException;
use Milky\Facades\Hooks;
use Milky\Impl\Arrayable;
use Milky\Impl\Jsonable;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger as MonologLogger;
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class Logger implements LoggerInterface
{
	/**
	 * The Monolog logger instance.
	 *
	 * @var MonologLogger
	 */
	protected $monolog;

	/**
	 * The Logging levels.
	 *
	 * @var array
	 */
	protected $levels = [
		'debug' => MonologLogger::DEBUG,
		'info' => MonologLogger::INFO,
		'notice' => MonologLogger::NOTICE,
		'warning' => MonologLogger::WARNING,
		'error' => MonologLogger::ERROR,
		'critical' => MonologLogger::CRITICAL,
		'alert' => MonologLogger::ALERT,
		'emergency' => MonologLogger::EMERGENCY,
	];

	/**
	 * Create a new log writer instance.
	 *
	 * @param  Logger $monolog
	 */
	public function __construct( MonologLogger $monolog )
	{
		$this->monolog = $monolog;
	}

	/**
	 * Logging an emergency message to the logs.
	 *
	 * @param  string $message
	 * @param  array $context
	 */
	public function emergency( $message, array $context = [] )
	{
		$this->writeLog( __FUNCTION__, $message, $context );
	}

	/**
	 * Logging an alert message to the logs.
	 *
	 * @param  string $message
	 * @param  array $context
	 */
	public function alert( $message, array $context = [] )
	{
		$this->writeLog( __FUNCTION__, $message, $context );
	}

	/**
	 * Logging a critical message to the logs.
	 *
	 * @param  string $message
	 * @param  array $context
	 */
	public function critical( $message, array $context = [] )
	{
		$this->writeLog( __FUNCTION__, $message, $context );
	}

	/**
	 * Logging an error message to the logs.
	 *
	 * @param  string $message
	 * @param  array $context
	 */
	public function error( $message, array $context = [] )
	{
		$this->writeLog( __FUNCTION__, $message, $context );
	}

	/**
	 * Logging a warning message to the logs.
	 *
	 * @param  string $message
	 * @param  array $context
	 */
	public function warning( $message, array $context = [] )
	{
		$this->writeLog( __FUNCTION__, $message, $context );
	}

	/**
	 * Logging a notice to the logs.
	 *
	 * @param  string $message
	 * @param  array $context
	 */
	public function notice( $message, array $context = [] )
	{
		$this->writeLog( __FUNCTION__, $message, $context );
	}

	/**
	 * Logging an informational message to the logs.
	 *
	 * @param  string $message
	 * @param  array $context
	 */
	public function info( $message, array $context = [] )
	{
		$this->writeLog( __FUNCTION__, $message, $context );
	}

	/**
	 * Logging a debug message to the logs.
	 *
	 * @param  string $message
	 * @param  array $context
	 */
	public function debug( $message, array $context = [] )
	{
		$this->writeLog( __FUNCTION__, $message, $context );
	}

	/**
	 * Logging a message to the logs.
	 *
	 * @param  string $level
	 * @param  string $message
	 * @param  array $context
	 */
	public function log( $level, $message, array $context = [] )
	{
		$this->writeLog( $level, $message, $context );
	}

	/**
	 * Dynamically pass log calls into the writer.
	 *
	 * @param  string $level
	 * @param  string $message
	 * @param  array $context
	 */
	public function write( $level, $message, array $context = [] )
	{
		$this->writeLog( $level, $message, $context );
	}

	/**
	 * Write a message to Monolog.
	 *
	 * @param  string $level
	 * @param  string $message
	 * @param  array $context
	 */
	protected function writeLog( $level, $message, $context )
	{
		$this->fireLogEvent( $level, $message = $this->formatMessage( $message ), $context );
		$this->monolog->{$level}( $message, $context );
	}

	/**
	 * Register a file log handler.
	 *
	 * @param  string $path
	 * @param  string $level
	 */
	public function useFiles( $path, $level = 'debug' )
	{
		$this->monolog->pushHandler( $handler = new StreamHandler( $path, $this->parseLevel( $level ) ) );

		$handler->setFormatter( $this->getDefaultFormatter() );
	}

	/**
	 * Register a daily file log handler.
	 *
	 * @param  string $path
	 * @param  int $days
	 * @param  string $level
	 */
	public function useDailyFiles( $path, $days = 0, $level = 'debug' )
	{
		$this->monolog->pushHandler( $handler = new RotatingFileHandler( $path, $days, $this->parseLevel( $level ) ) );

		$handler->setFormatter( $this->getDefaultFormatter() );
	}

	/**
	 * Register a Syslog handler.
	 *
	 * @param  string $name
	 * @param  string $level
	 * @return LoggerInterface
	 */
	public function useSyslog( $name = 'framework', $level = 'debug' )
	{
		return $this->monolog->pushHandler( new SyslogHandler( $name, LOG_USER, $level ) );
	}

	/**
	 * Register an error_log handler.
	 *
	 * @param  string $level
	 * @param  int $messageType
	 */
	public function useErrorLog( $level = 'debug', $messageType = ErrorLogHandler::OPERATING_SYSTEM )
	{
		$this->monolog->pushHandler( $handler = new ErrorLogHandler( $messageType, $this->parseLevel( $level ) ) );

		$handler->setFormatter( $this->getDefaultFormatter() );
	}

	/**
	 * Register a new callback handler for when a log event is triggered.
	 *
	 * @param  \Closure $callback
	 *
	 * @throws \RuntimeException
	 */
	public function listen( Closure $callback )
	{
		if ( !isset( $this->dispatcher ) )
			throw new RuntimeException( 'Events dispatcher has not been set.' );

		Hooks::addHook( 'logger', $callback );
	}

	/**
	 * Fires a log event.
	 *
	 * @param  string $level
	 * @param  string $message
	 * @param  array $context
	 */
	protected function fireLogEvent( $level, $message, array $context = [] )
	{
		// If the event dispatcher is set, we will pass along the parameters to the
		// log listeners. These are useful for building profilers or other tools
		// that aggregate all of the log messages for a given "request" cycle.
		if ( isset( $this->dispatcher ) )
			Hooks::trigger( 'logger.' . $level, compact( 'level', 'message', 'context' ) );
	}

	/**
	 * Format the parameters for the logger.
	 *
	 * @param  mixed $message
	 * @return mixed
	 */
	protected function formatMessage( $message )
	{
		if ( is_array( $message ) )
			return var_export( $message, true );
		elseif ( $message instanceof Jsonable )
			return $message->toJson();
		elseif ( $message instanceof Arrayable )
			return var_export( $message->toArray(), true );

		return $message;
	}

	/**
	 * Parse the string level into a Monolog constant.
	 *
	 * @param  string $level
	 * @return int
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function parseLevel( $level )
	{
		if ( isset( $this->levels[$level] ) )
			return $this->levels[$level];

		throw new InvalidArgumentException( 'Invalid log level.' );
	}

	/**
	 * Get the underlying Monolog instance.
	 *
	 * @return Logger
	 */
	public function getMonolog()
	{
		return $this->monolog;
	}

	/**
	 * Get a default Monolog formatter instance.
	 *
	 * @return LineFormatter
	 */
	protected function getDefaultFormatter()
	{
		return new LineFormatter( null, null, true, true );
	}
}
