<?php namespace Milky;

use Milky\Cache\CacheManager;
use Milky\Cache\Console\ClearCommand;
use Milky\Cache\MemcachedConnector;
use Milky\Config\Configuration;
use Milky\Config\ConfigurationBuilder;
use Milky\Encryption\Encrypter;
use Milky\Exceptions\FrameworkException;
use Milky\Facades\Log;
use Milky\Filesystem\Filesystem;
use Milky\Hashing\BcryptHasher;
use Milky\Helpers\Arr;
use Milky\Helpers\Str;
use Milky\Hooks\HookDispatcher;
use Milky\Http\Session\SessionManager;
use Milky\Logging\LogBuilder;
use Milky\Logging\Logger;
use Milky\Providers\ProviderRepository;

/**
 * @Product: Milky Framework
 * @Version 6.0.0 (Polkadot)
 * @Last Updated: August 2016
 * @PHP Version: 5.5.9 or Newer
 *
 * @Author: Penoaks Publishing Co.
 * @E-Mail: development@penoaks.com
 * @Website: http://penoaks.com
 *
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class Framework implements \ArrayAccess
{
	use Globals;

	const PRODUCT = "Milky Framework";
	const VERSION = "v6.0.0 (Polkadot)";
	const COPYRIGHT = "Copyright © 2016 Penoaks Publishing Co.";

	/**
	 * @var bool
	 */
	private $isBooted = false;

	/**
	 * The Hook Dispatcher
	 *
	 * @var HookDispatcher
	 */
	public $hooks;

	/**
	 * The Configuration
	 *
	 * @var Configuration
	 */
	public $config;

	/**
	 * The Logger
	 *
	 * @var Logger
	 */
	public $log;

	/**
	 * The Provider Repository
	 *
	 * @var ProviderRepository
	 */
	public $providers;

	/**
	 * The application base path
	 *
	 * @var string
	 */
	public $basePath;

	/**
	 * @var array
	 */
	private $paths = [];

	/**
	 * A custom callback used to configure Monolog.
	 *
	 * @var callable|null
	 */
	protected $monologConfigurator;

	/**
	 * Framework Constructor
	 */
	public function __construct( $basePath )
	{
		if ( $this->offsetExists( 'fw' ) )
			throw new FrameworkException( "Framework is already running." );
		$this['fw'] = $this;

		$this->basePath = $basePath;

		$this->hooks = new HookDispatcher();

		$this->hooks->addHook( ['log'], function ()
		{
			echo "Hook 1";
		} );

		$this->paths = [
			'src' => ['src'],
			'config' => ['__fw', 'config'],
			'cache' => ['__fw', 'cache'],
			'database' => ['__fw', 'database'],
			'logs' => ['__fw', 'logs'],
			'lang' => ['__src', 'lang'],
			'fw' => ['fw'],
			'public' => ['__base'],
		];

		$this->config = ConfigurationBuilder::build( $this );

		$this->paths = array_merge( $this->paths, $this->config->get( 'app.paths', [] ) );

		$this->log = LogBuilder::build( $this );

		$this->log->info( "Milky Framework Loading" );

		$this->providers = new ProviderRepository();

		foreach ( $this->config->get( 'app.providers', [] ) as $provider )
			$this->providers->register( $provider );

		$this->registerBindingAlias();

		$this->hooks->addHook( 'binding.failed', [$this, 'findServiceBinding'] );

		$this->hooks->trigger( 'fw.loaded', $this );
		$this->log->info( "Milky Framework Loaded" );
	}

	public function registerBindingAlias()
	{
		$this->addAlias( ['Milky\Encryption\Encrypter'], 'encrypter' );
	}

	public function findServiceBinding( $binding )
	{
		switch ( $binding )
		{
			case 'encrypter':
			{
				$config = $this->config->get( 'app' );

				if ( Str::startsWith( $key = $config['key'], 'base64:' ) )
					$key = base64_decode( substr( $key, 7 ) );

				static::set( 'encrypter', $this->getEncrypterForKeyAndCipher( $key, $config['cipher'] ) );
				break;
			}
			case 'session':
			{
				static::set( 'session', new SessionManager( $this ) );
				break;
			}
			case 'session.store':
			{
				// First, we will create the session manager which is responsible for the
				// creation of the various session drivers when they are needed by the
				// application instance, and will resolve them on a lazy load basis.
				static::set( 'session.store', static::get( 'session' )->driver() );
				break;
			}
			case 'hash':
				static::set( 'hash', new BcryptHasher );
				break;
			case 'files':
			{
				static::set( 'files', new Filesystem() );
				break;
			}
			case 'cache':
			{
				static::set( 'cache', new CacheManager() );
				break;
			}
			case 'cache.store':
			{
				static::set( 'cache.store', static::get( 'cache' )->driver() );
				break;
			}
			case 'memcached.connector':
			{
				static::set( 'memcached.connector', new MemcachedConnector() );
				break;
			}
			case 'command.cache.clear':
			{
				static::set( 'command.cache.clear', new ClearCommand( static::get( 'cache' ) ) );
				// $this->console->addCommand( 'command.cache.clear' );
				break;
			}
		}
	}

	/**
	 * Get the proper encrypter instance for the given key and cipher.
	 *
	 * @param  string $key
	 * @param  string $cipher
	 * @return mixed
	 *
	 * @throws \RuntimeException
	 */
	protected function getEncrypterForKeyAndCipher( $key, $cipher )
	{
		if ( Encrypter::supported( $key, $cipher ) )
			return new Encrypter( $key, $cipher );
		else
			throw new FrameworkException( 'No supported encrypter found. The cipher and / or key length are invalid.' );
	}

	/**
	 * @return Framework
	 */
	public static function fw()
	{
		return static::$globals['fw'];
	}

	/**
	 * @return bool
	 */
	public static function isRunning()
	{
		return array_key_exists( 'fw', static::$globals );
	}

	public function boot()
	{
		if ( $this->isBooted )
			throw new FrameworkException( "This framework has already been booted." );
		$this->isBooted = true;

		$this->hooks->trigger( 'fw.booting', $this );
		Log::info( "Milky Framework Booted" );

		$this->providers->boot();

		$this->hooks->trigger( 'fw.booted', $this );
		Log::info( "Milky Framework Booted" );
	}

	public function isBooted()
	{
		return $this->isBooted;
	}

	public function newHttpFactory()
	{
		return new Http\Factory( $this );
	}

	public function getProduct()
	{
		return static::PRODUCT;
	}

	public function getVersion()
	{
		return static::VERSION;
	}

	public function getCopyright()
	{
		return static::COPYRIGHT;
	}

	/**
	 * Append args to the base path
	 *
	 * @return string
	 */
	public function buildPath()
	{
		$slugs = func_get_args();

		if ( is_array( $slugs[0] ) )
			$slugs = $slugs[0];

		if ( count( $slugs ) == 0 )
			return $this->basePath;

		if ( Str::startsWith( $slugs[0], '__' ) )
		{
			$key = substr( $slugs[0], 2 );
			if ( $key == 'base' )
				$slugs[0] = $this->basePath;
			else if ( array_key_exists( $key, $this->paths ) )
			{
				$paths = $this->paths[$key];
				if ( is_array( $paths ) )
				{
					unset( $slugs[0] );
					foreach ( array_reverse( $paths ) as $slug )
						$slugs = Arr::prepend( $slugs, $slug );
				}
				else
					$slugs[0] = $paths;
			}
			else
				throw new FrameworkException( "Path [" . $key . "] is not set" );

			return $this->buildPath( $slugs );
		}
		else
			$slugs = Arr::prepend( $slugs, $this->basePath );

		return implode( DIRECTORY_SEPARATOR, $slugs );
	}

	/**
	 * Define a callback to be used to configure Monolog.
	 *
	 * @param  callable $callback
	 * @return $this
	 */
	public function configureMonologUsing( callable $callback )
	{
		$this->monologConfigurator = $callback;

		return $this;
	}

	/**
	 * Determine if the application has a custom Monolog configurator.
	 *
	 * @return bool
	 */
	public function hasMonologConfigurator()
	{
		return !is_null( $this->monologConfigurator );
	}

	/**
	 * Get the custom Monolog configurator for the application.
	 *
	 * @return callable
	 */
	public function getMonologConfigurator()
	{
		return $this->monologConfigurator;
	}

	/**
	 * Determine if the application is currently down for maintenance.
	 *
	 * @return bool
	 */
	public function isDownForMaintenance()
	{
		return false;
	}

	/**
	 * Get or check the current application environment.
	 *
	 * @param  mixed
	 * @return string
	 */
	public function environment()
	{
		$env = $this->config->get( 'app.env', 'production' );

		if ( func_num_args() > 0 )
		{
			$patterns = is_array( func_get_arg( 0 ) ) ? func_get_arg( 0 ) : func_get_args();

			foreach ( $patterns as $pattern )
				if ( Str::is( $pattern, $env ) )
					return true;

			return false;
		}

		return $env;
	}
}
