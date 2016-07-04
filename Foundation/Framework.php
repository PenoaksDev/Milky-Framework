<?php
namespace Foundation;

use Closure;
use Composer\Autoload\ClassLoader;
use Foundation\Autoload\Paths;
use Foundation\Bindings\Bindings;
use Foundation\Config\Repository;
use Foundation\Contracts\Console\Kernel as ConsoleKernel;
use Foundation\Contracts\Debug\ExceptionHandler;
use Foundation\Contracts\Http\Kernel as HttpKernel;
use Foundation\Events\EventServiceProvider;
use Foundation\Events\Runlevel;
use Foundation\Filesystem\Filesystem;
use Foundation\Http\Kernel;
use Foundation\Http\Request;
use Foundation\Routing\RoutingServiceProvider;
use Foundation\Support\Arr;
use Foundation\Support\ServiceProvider;
use Foundation\Support\Str;
use RuntimeException;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Debug\ExceptionHandler as SymfonyExceptionHandler;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @Product: Penoaks Framework
 * @Version 6.0.0 (Code Revival)
 * @Last Updated: July 2016
 * @PHP Version: 5.5.9 or Newer
 *
 * @Author: Penoaks Publishing Co.
 * @E-Mail: development@penoaks.com
 * @Website: http://penoaks.com
 * @License: MIT License
 * @Copyright (C) 2016 Penoaks Publishing Co. All Rights Reserved.
 *
 * Description:
 * This file is the primary constructor for Penoaks Framework.
 */
class Framework implements HttpKernelInterface
{
	/**
	 * The Penoaks Framework version.
	 *
	 * @var string
	 */
	const VERSION = '6.0.0';

	/**
	 * Stores a static instance of this framework
	 *
	 * @var $this
	 */
	protected static $framework;

	/**
	 * Stores the autoloader for later use.
	 *
	 * @var ClassLoader
	 */
	protected $loader;

	/**
	 * Stores the framework runlevel
	 *
	 * @var Runlevel
	 */
	public $runlevel;

	/**
	 * Stores the Config instance.
	 *
	 * @var Repository
	 */
	public $config;

	/**
	 * Stores the framework kernel.
	 *
	 * @var \Foundation\Http\Kernel
	 */
	public $kernel;

	/**
	 * Stores the Service Bindings instance.
	 *
	 * @var \Foundation\Bindings\Bindings
	 */
	public $bindings;

	/**
	 * Indicates if the application has "booted".
	 *
	 * @var bool
	 */
	protected $booted = false;

	/**
	 * The array of booting callbacks.
	 *
	 * @var array
	 */
	protected $bootingCallbacks = [];

	/**
	 * The array of booted callbacks.
	 *
	 * @var array
	 */
	protected $bootedCallbacks = [];

	/**
	 * The array of terminating callbacks.
	 *
	 * @var array
	 */
	protected $terminatingCallbacks = [];

	/**
	 * All of the registered service providers.
	 *
	 * @var array
	 */
	protected $serviceProviders = [];

	/**
	 * The names of the loaded service providers.
	 *
	 * @var array
	 */
	protected $loadedProviders = [];

	/**
	 * The deferred services and their providers.
	 *
	 * @var array
	 */
	protected $deferredServices = [];

	/**
	 * A custom callback used to configure Monolog.
	 *
	 * @var callable|null
	 */
	protected $monologConfigurator;

	/**
	 * The environment file to load during bootstrapping.
	 *
	 * @var string
	 */
	protected $environmentFile = '.env';

	/**
	 * The application namespace.
	 *
	 * @var string
	 */
	protected $namespace = null;

	/**
	 * Returns the current instance of this framework class
	 *
	 * @return $this
	 */
	public static function i()
	{
		return static::$framework;
	}

	/**
	 * Create a new Foundation application instance.
	 *
	 * @param array $params
	 * @param array $paths
	 * @param ClassLoader $loader ;
	 */
	public function __construct( array $params, array $paths, ClassLoader $loader )
	{
		if ( !is_null( static::$framework ) )
		{
			throw new RuntimeException( "The Framework has already been initialed." );
		}

		Paths::set( $paths );

		$this->bindings = new Bindings( $this );
		$this->loader = $loader;

		$loader->set( "", Paths::get( 'src' ) );

		$this->register( new EventServiceProvider( $this ) );
		$this->register( new RoutingServiceProvider( $this ) );

		$this->bindings['router']->get('/', function(){
			return "Hello World!";
		});

		$aliases = ['fw' => ['Foundation\Framework'],
			'auth' => ['Foundation\Auth\AuthManager', 'Foundation\Contracts\Auth\Factory'],
			'auth.driver' => ['Foundation\Contracts\Auth\Guard'],
			'blade.compiler' => ['Foundation\View\Compilers\BladeCompiler'],
			'cache' => ['Foundation\Cache\CacheManager', 'Foundation\Contracts\Cache\Factory'],
			'cache.store' => ['Foundation\Cache\Repository', 'Foundation\Contracts\Cache\Repository'],
			'config' => ['Foundation\Config\Repository', 'Foundation\Contracts\Config\Repository'],
			'cookie' => ['Foundation\Cookie\CookieJar',
				'Foundation\Contracts\Cookie\Factory',
				'Foundation\Contracts\Cookie\QueueingFactory'],
			'encrypter' => ['Foundation\Encryption\Encrypter', 'Foundation\Contracts\Encryption\Encrypter'],
			'db' => ['Foundation\Database\DatabaseManager'],
			'db.connection' => ['Foundation\Database\Connection', 'Foundation\Database\ConnectionInterface'],
			'events' => ['Foundation\Events\Dispatcher', 'Foundation\Contracts\Events\Dispatcher'],
			'files' => ['Foundation\Filesystem\Filesystem'],
			'filesystem' => ['Foundation\Filesystem\FilesystemManager', 'Foundation\Contracts\Filesystem\Factory'],
			'filesystem.disk' => ['Foundation\Contracts\Filesystem\Filesystem'],
			'filesystem.cloud' => ['Foundation\Contracts\Filesystem\Cloud'],
			'hash' => ['Foundation\Contracts\Hashing\Hasher'],
			'translator' => ['Foundation\Translation\Translator', 'Symfony\Component\Translation\TranslatorInterface'],
			'log' => ['Foundation\Log\Writer', 'Foundation\Contracts\Logging\Log', 'Psr\Log\LoggerInterface'],
			'mailer' => ['Foundation\Mail\Mailer',
				'Foundation\Contracts\Mail\Mailer',
				'Foundation\Contracts\Mail\MailQueue'],
			'auth.password' => ['Foundation\Auth\Passwords\PasswordBrokerManager',
				'Foundation\Contracts\Auth\PasswordBrokerFactory'],
			'auth.password.broker' => ['Foundation\Auth\Passwords\PasswordBroker',
				'Foundation\Contracts\Auth\PasswordBroker'],
			'queue' => ['Foundation\Queue\QueueManager',
				'Foundation\Contracts\Queue\Factory',
				'Foundation\Contracts\Queue\Monitor'],
			'queue.connection' => ['Foundation\Contracts\Queue\Queue'],
			'queue.failer' => ['Foundation\Queue\Failed\FailedJobProviderInterface'],
			'redirect' => ['Foundation\Routing\Redirector'],
			'redis' => ['Foundation\Redis\Database', 'Foundation\Contracts\Redis\Database'],
			'request' => ['Foundation\Http\Request', 'Symfony\Component\HttpFoundation\Request'],
			'router' => ['Foundation\Routing\Router', 'Foundation\Contracts\Routing\Registrar'],
			'session' => ['Foundation\Session\SessionManager'],
			'session.store' => ['Foundation\Session\Store',
				'Symfony\Component\HttpFoundation\Session\SessionInterface'],
			'url' => ['Foundation\Routing\UrlGenerator', 'Foundation\Contracts\Routing\UrlGenerator'],
			'validator' => ['Foundation\Validation\Factory', 'Foundation\Contracts\Validation\Factory'],
			'view' => ['Foundation\View\Factory', 'Foundation\Contracts\View\Factory'],];

		foreach ( $aliases as $key => $aliases2 )
		{
			foreach ( $aliases2 as $alias )
			{
				$this->bindings->alias( $key, $alias );
			}
		}

		$this->bindings->singleton( HttpKernel::class, $params['httpKernel'] );
		$this->bindings->singleton( ConsoleKernel::class, $params['consoleKernel'] );
		$this->bindings->singleton( ExceptionHandler::class, $params['exceptionHandler'] );

		$this->kernel = $this->bindings->make( Kernel::class );

		$this->runlevel = new Runlevel();

		$this->bindings['events']->fire( $this->runlevel->set( Runlevel::INITIALIZING ), [$this] );

		$this->bindings['events']->fire( $this->runlevel->set( Runlevel::BOOTSTRAPPED ), [$this] );
	}

	/**
	 * Returns the current runlevel instance of the framework
	 *
	 * @return Runlevel
	 */
	public function runlevel()
	{
		return $this->runlevel;
	}

	/**
	 * Checks the current runlevel
	 *
	 * @param mixed $level
	 * @return bool
	 */
	public function isRunlevel( $level )
	{
		return $this->runlevel->get() == $level || strtolower( $this->runlevel->asString() ) == strtolower( $level );
	}

	/**
	 * Get the version number of the application.
	 *
	 * @return string
	 */
	public function version()
	{
		return static::VERSION;
	}

	/**
	 * Run the given array of bootstrap classes.
	 *
	 * @param  array $bootstrappers
	 * @return void
	 */
	public function bootstrap( array $bootstrappers )
	{
		if ( $this->runlevel->get() <> Runlevel::INITIALIZING )
		{
			throw new RuntimeException( "You can not bootstrap in any other runlevel besides INITIALIZING" );
		}

		foreach ( $bootstrappers as $bootstrapper )
		{
			$this->bindings['events']->fire( 'bootstrapping: ' . $bootstrapper, [$this] );
			$this->bindings->make( $bootstrapper )->bootstrap( $this );
			$this->bindings['events']->fire( 'bootstrapped: ' . $bootstrapper, [$this] );
		}
	}

	/**
	 * Register a callback to run after loading the environment.
	 *
	 * @param  \Closure $callback
	 * @return void
	 */
	public function afterLoadingEnvironment( Closure $callback )
	{
		$this->afterBootstrapping( 'Foundation\Bootstrap\DetectEnvironment', $callback );
	}

	/**
	 * Register a callback to run before a bootstrapper.
	 *
	 * @param  string $bootstrapper
	 * @param  Closure $callback
	 * @return void
	 */
	public function beforeBootstrapping( $bootstrapper, Closure $callback )
	{
		$this->bindings['events']->listen( 'bootstrapping: ' . $bootstrapper, $callback );
	}

	/**
	 * Register a callback to run after a bootstrapper.
	 *
	 * @param  string $bootstrapper
	 * @param  Closure $callback
	 * @return void
	 */
	public function afterBootstrapping( $bootstrapper, Closure $callback )
	{
		$this->bindings['events']->listen( 'bootstrapped: ' . $bootstrapper, $callback );
	}

	/**
	 * Bind all of the application paths in the bindings.
	 *
	 * @return void
	 */
	protected function bindPathsInBindings()
	{
		// TODO Replace the end-point of theses with the Paths class
		$this->bindings->instance( 'path', $this->path() );
		$this->bindings->instance( 'path.base', $this->basePath() );
		$this->bindings->instance( 'path.lang', $this->langPath() );
		$this->bindings->instance( 'path.config', $this->configPath() );
		$this->bindings->instance( 'path.public', $this->publicPath() );
		$this->bindings->instance( 'path.storage', $this->storagePath() );
		$this->bindings->instance( 'path.database', $this->databasePath() );
		$this->bindings->instance( 'path.bootstrap', $this->bootstrapPath() );
	}

	/**
	 * Get the path to the application "app" directory.
	 *
	 * @return string
	 */
	public function path()
	{
		return Paths::get( 'src' );
	}

	/**
	 * Get the base path of the framework installation.
	 *
	 * @return string
	 */
	public function basePath()
	{
		return Paths::get( 'base' );
	}

	/**
	 * Get the path to the bootstrap directory.
	 *
	 * @return string
	 */
	public function bootstrapPath()
	{
		return Paths::get( 'bootstrap' );
	}

	/**
	 * Get the path to the application configuration files.
	 *
	 * @return string
	 */
	public function configPath()
	{
		return Paths::get( 'config' );
	}

	/**
	 * Get the path to the database directory.
	 *
	 * @return string
	 */
	public function databasePath()
	{
		return Paths::get( 'database' );
	}

	/**
	 * Set the database directory.
	 *
	 * @param  string $path
	 * @return $this
	 */
	public function useDatabasePath( $path )
	{
		Paths::set( ['database' => $path] );
		$this->bindings->instance( 'path.database', $path );

		return $this;
	}

	/**
	 * Get the path to the language files.
	 *
	 * @return string
	 */
	public function langPath()
	{
		return Paths::get( 'lang' );
	}

	/**
	 * Get the path to the public / web directory.
	 *
	 * @return string
	 */
	public function publicPath()
	{
		return Paths::get( 'public' );
	}

	/**
	 * Get the path to the storage directory.
	 *
	 * @return string
	 */
	public function storagePath()
	{
		return Paths::get( 'storage' );
	}

	/**
	 * Set the storage directory.
	 *
	 * @param  string $path
	 * @return $this
	 */
	public function useStoragePath( $path )
	{
		Paths::set( ['storage' => $path] );
		$this->bindings->instance( 'path.storage', $path );

		return $this;
	}

	/**
	 * Get the path to the environment file directory.
	 *
	 * @return string
	 */
	public function environmentPath()
	{
		return Paths::get( 'env' );
	}

	/**
	 * Set the directory for the environment file.
	 *
	 * @param  string $path
	 * @return $this
	 */
	public function useEnvironmentPath( $path )
	{
		Paths::set( ['env' => $path] );

		return $this;
	}

	/**
	 * Set the environment file to be loaded during bootstrapping.
	 *
	 * @param  string $file
	 * @return $this
	 */
	public function loadEnvironmentFrom( $file )
	{
		$this->environmentFile = $file;

		return $this;
	}

	/**
	 * Get the environment file the application is using.
	 *
	 * @return string
	 */
	public function environmentFile()
	{
		return $this->environmentFile ?: '.env';
	}

	/**
	 * Get the fully qualified path to the environment file.
	 *
	 * @return string
	 */
	public function environmentFilePath()
	{
		return $this->environmentPath() . '/' . $this->environmentFile();
	}

	/**
	 * Get or check the current application environment.
	 *
	 * @param  mixed
	 * @return string|bool
	 */
	public function environment()
	{
		if ( func_num_args() > 0 )
		{
			$patterns = is_array( func_get_arg( 0 ) ) ? func_get_arg( 0 ) : func_get_args();

			foreach ( $patterns as $pattern )
			{
				if ( Str::is( $pattern, $this->bindings['env'] ) )
				{
					return true;
				}
			}

			return false;
		}

		return $this->bindings['env'];
	}

	/**
	 * Determine if application is in local environment.
	 *
	 * @return bool
	 */
	public function isLocal()
	{
		return $this->bindings['env'] == 'local';
	}

	/**
	 * Detect the application's current environment.
	 *
	 * @param  \Closure $callback
	 * @return string
	 */
	public function detectEnvironment( Closure $callback )
	{
		$args = isset( $_SERVER['argv'] ) ? $_SERVER['argv'] : null;

		return $this->bindings['env'] = ( new EnvironmentDetector() )->detect( $callback, $args );
	}

	/**
	 * Determine if we are running in the console.
	 *
	 * @return bool
	 */
	public function runningInConsole()
	{
		return php_sapi_name() == 'cli';
	}

	/**
	 * Determine if we are running unit tests.
	 *
	 * @return bool
	 */
	public function runningUnitTests()
	{
		return $this->bindings['env'] == 'testing';
	}

	/**
	 * Register all of the configured providers.
	 *
	 * @return void
	 */
	public function registerConfiguredProviders()
	{
		$manifestPath = $this->getCachedServicesPath();

		( new ProviderRepository( $this, new Filesystem, $manifestPath ) )->load( $this->config['app.providers'] );
	}

	/**
	 * Register a service provider with the application.
	 *
	 * @param  \Foundation\Support\ServiceProvider|string $provider
	 * @param  array $options
	 * @param  bool $force
	 * @return \Foundation\Support\ServiceProvider
	 */
	public function register( $provider, $options = [], $force = false )
	{
		if ( ( $registered = $this->getProvider( $provider ) ) && !$force )
		{
			return $registered;
		}

		// If the given "provider" is a string, we will resolve it, passing in the
		// application instance automatically for the developer. This is simply
		// a more convenient way of specifying your service provider classes.
		if ( is_string( $provider ) )
		{
			$provider = $this->resolveProviderClass( $provider );
		}

		$provider->register();

		// Once we have registered the service we will iterate through the options
		// and set each of them on the application so they will be available on
		// the actual loading of the service objects and for developer usage.
		foreach ( $options as $key => $value )
		{
			$this->bindings[$key] = $value;
		}

		$this->markAsRegistered( $provider );

		// If the application has already booted, we will call this boot method on
		// the provider class so it has an opportunity to do its boot logic and
		// will be ready for any usage by the developer's application logic.
		if ( $this->booted )
		{
			$this->bootProvider( $provider );
		}

		return $provider;
	}

	/**
	 * Get the registered service provider instance if it exists.
	 *
	 * @param  \Foundation\Support\ServiceProvider|string $provider
	 * @return \Foundation\Support\ServiceProvider|null
	 */
	public function getProvider( $provider )
	{
		$name = is_string( $provider ) ? $provider : get_class( $provider );

		return Arr::first( $this->serviceProviders, function ( $key, $value ) use ( $name )
		{
			return $value instanceof $name;
		} );
	}

	/**
	 * Resolve a service provider instance from the class name.
	 *
	 * @param  string $provider
	 * @return \Foundation\Support\ServiceProvider
	 */
	public function resolveProviderClass( $provider )
	{
		return new $provider( $this );
	}

	/**
	 * Mark the given provider as registered.
	 *
	 * @param  \Foundation\Support\ServiceProvider $provider
	 * @return void
	 */
	protected function markAsRegistered( $provider )
	{
		$this->bindings['events']->fire( $class = get_class( $provider ), [$provider] );

		$this->serviceProviders[] = $provider;

		$this->loadedProviders[$class] = true;
	}

	/**
	 * Load and boot all of the remaining deferred providers.
	 *
	 * @return void
	 */
	public function loadDeferredProviders()
	{
		// We will simply spin through each of the deferred providers and register each
		// one and boot them if the application has booted. This should make each of
		// the remaining services available to this application for immediate use.
		foreach ( $this->deferredServices as $service => $provider )
		{
			$this->loadDeferredProvider( $service, $provider );
		}

		$this->deferredServices = [];
	}

	public function loadDeferredProvider( $service, $provider = null )
	{
		if ( is_null( $provider ) )
		{
			if ( !isset( $this->deferredServices[$service] ) )
			{
				return;
			}

			$provider = $this->deferredServices[$service];
		}

		// If the service provider has not already been loaded and registered we can
		// register it with the application and remove the service from this list
		// of deferred services, since it will already be loaded on subsequent.
		if ( !isset( $this->loadedProviders[$provider] ) )
		{
			// Once the provider that provides the deferred service has been registered we
			// will remove it from our local list of the deferred services with related
			// providers so that this container does not try to resolve it out again.
			if ( $service )
			{
				unset( $this->deferredServices[$service] );
			}

			$this->register( $instance = new $provider( $this ) );

			if ( !$this->booted )
			{
				$this->booting( function () use ( $instance )
				{
					$this->bootProvider( $instance );
				} );
			}

		}

	}

	/**
	 * Resolve the given type from the bindings.
	 * Normally called from the Bindings class
	 *
	 * @param  string $abstract
	 */
	public function make( $abstract )
	{
		if ( $this->bound( $abstract ) )
		{
			$this->loadDeferredProvider( $abstract );
		}
	}

	/**
	 * Determine if the given abstract type has been bound.
	 * Normally called from Bindings class.
	 *
	 * @param  string $abstract
	 * @return bool
	 */
	public function bound( $abstract )
	{
		return isset( $this->deferredServices[$abstract] );
	}

	/**
	 * Flush the bindings of all bindings and resolved instances.
	 * Normally called from the Bindings class.
	 *
	 * @return void
	 */
	public function flush()
	{
		$this->loadedProviders = [];
	}

	/**
	 * Determine if the application has booted.
	 *
	 * @return bool
	 */
	public function isBooted()
	{
		return $this->booted;
	}

	/**
	 * Boot the application's service providers.
	 *
	 * @return void
	 */
	public function boot()
	{
		if ( $this->booted )
		{
			return;
		}

		// Once the application has booted we will also fire some "booted" callbacks
		// for any listeners that need to do work after this initial booting gets
		// finished. This is useful when ordering the boot-up processes we run.
		$this->fireAppCallbacks( $this->bootingCallbacks );

		array_walk( $this->serviceProviders, function ( $p )
		{
			$this->bootProvider( $p );
		} );

		$this->booted = true;

		$this->fireAppCallbacks( $this->bootedCallbacks );
	}

	/**
	 * Boot the given service provider.
	 *
	 * @param  \Foundation\Support\ServiceProvider $provider
	 * @return mixed
	 */
	protected function bootProvider( ServiceProvider $provider )
	{
		if ( method_exists( $provider, 'boot' ) )
		{
			return $this->bindings->call( [$provider, 'boot'] );
		}

		return false;
	}

	/**
	 * Register a new boot listener.
	 *
	 * @param  mixed $callback
	 * @return void
	 */
	public function booting( $callback )
	{
		$this->bootingCallbacks[] = $callback;
	}

	/**
	 * Register a new "booted" listener.
	 *
	 * @param  mixed $callback
	 * @return void
	 */
	public function booted( $callback )
	{
		$this->bootedCallbacks[] = $callback;

		if ( $this->isBooted() )
		{
			$this->fireAppCallbacks( [$callback] );
		}
	}

	/**
	 * Call the booting callbacks for the application.
	 *
	 * @param  array $callbacks
	 * @return void
	 */
	protected function fireAppCallbacks( array $callbacks )
	{
		foreach ( $callbacks as $callback )
		{
			call_user_func( $callback, $this );
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function handle( SymfonyRequest $request, $type = self::MASTER_REQUEST, $catch = true )
	{
		return $this->bindings['Foundation\Contracts\Http\Kernel']->handle( Request::createFromBase( $request ) );
	}

	/**
	 * Determine if middleware has been disabled for the application.
	 *
	 * @return bool
	 */
	public function shouldSkipMiddleware()
	{
		return $this->bound( 'middleware.disable' ) && $this->make( 'middleware.disable' ) === true;
	}

	/**
	 * Determine if the application configuration is cached.
	 *
	 * @return bool
	 */
	public function configurationIsCached()
	{
		return file_exists( $this->getCachedConfigPath() );
	}

	/**
	 * Get the path to the configuration cache file.
	 *
	 * @return string
	 */
	public function getCachedConfigPath()
	{
		return $this->bootstrapPath() . '/cache/config.php';
	}

	/**
	 * Determine if the application routes are cached.
	 *
	 * @return bool
	 */
	public function routesAreCached()
	{
		return $this->bindings['files']->exists( $this->getCachedRoutesPath() );
	}

	/**
	 * Get the path to the routes cache file.
	 *
	 * @return string
	 */
	public function getCachedRoutesPath()
	{
		return $this->bootstrapPath() . '/cache/routes.php';
	}

	/**
	 * Get the path to the cached "compiled.php" file.
	 *
	 * @return string
	 */
	public function getCachedCompilePath()
	{
		return $this->bootstrapPath() . '/cache/compiled.php';
	}

	/**
	 * Get the path to the cached services.php file.
	 *
	 * @return string
	 */
	public function getCachedServicesPath()
	{
		return $this->bootstrapPath() . '/cache/services.php';
	}

	/**
	 * Determine if the application is currently down for maintenance.
	 *
	 * @return bool
	 */
	public function isDownForMaintenance()
	{
		return file_exists( $this->storagePath() . '/framework/down' );
	}

	/**
	 * Throw an HttpException with the given data.
	 *
	 * @param  int $code
	 * @param  string $message
	 * @param  array $headers
	 * @return void
	 *
	 * @throws \Symfony\Component\HttpKernel\Exception\HttpException
	 */
	public function abort( $code, $message = '', array $headers = [] )
	{
		if ( $code == 404 )
		{
			throw new NotFoundHttpException( $message );
		}

		throw new HttpException( $code, $message, null, $headers );
	}

	/**
	 * Register a terminating callback with the application.
	 *
	 * @param  \Closure $callback
	 * @return $this
	 */
	public function terminating( Closure $callback )
	{
		$this->terminatingCallbacks[] = $callback;

		return $this;
	}

	/**
	 * Terminate the application.
	 *
	 * @return void
	 */
	public function terminate()
	{
		foreach ( $this->terminatingCallbacks as $terminating )
		{
			$this->bindings->call( $terminating );
		}
	}

	/**
	 * Get the service providers that have been loaded.
	 *
	 * @return array
	 */
	public function getLoadedProviders()
	{
		return $this->loadedProviders;
	}

	/**
	 * Get the application's deferred services.
	 *
	 * @return array
	 */
	public function getDeferredServices()
	{
		return $this->deferredServices;
	}

	/**
	 * Set the application's deferred services.
	 *
	 * @param  array $services
	 * @return void
	 */
	public function setDeferredServices( array $services )
	{
		$this->deferredServices = $services;
	}

	/**
	 * Add an array of services to the application's deferred services.
	 *
	 * @param  array $services
	 * @return void
	 */
	public function addDeferredServices( array $services )
	{
		$this->deferredServices = array_merge( $this->deferredServices, $services );
	}

	/**
	 * Determine if the given service is a deferred service.
	 *
	 * @param  string $service
	 * @return bool
	 */
	public function isDeferredService( $service )
	{
		return isset( $this->deferredServices[$service] );
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
	 * Get the current application locale.
	 *
	 * @return string
	 */
	public function getLocale()
	{
		return $this->bindings['config']->get( 'app.locale' );
	}

	/**
	 * Set the current application locale.
	 *
	 * @param  string $locale
	 * @return void
	 */
	public function setLocale( $locale )
	{
		$this->bindings['config']->set( 'app.locale', $locale );

		$this->bindings['translator']->setLocale( $locale );

		$this->bindings['events']->fire( 'locale.changed', [$locale] );
	}

	/**
	 * Determine if application locale is the given locale.
	 *
	 * @param  string $locale
	 * @return bool
	 */
	public function isLocale( $locale )
	{
		return $this->getLocale() == $locale;
	}

	/**
	 * Get the application namespace.
	 *
	 * @return string
	 *
	 * @throws \RuntimeException
	 */
	public function getNamespace()
	{
		if ( is_null( $this->namespace ) )
		{
			$ns = explode( '\\', $this->bindings['config']['main.httpKernel'] );

			if ( count( $ns ) > 0 )
			{
				$ns = array_slice( $ns, 0, count( $ns ) - 1 );
			}

			$this->namespace = implode( '\\', $ns );
		}

		return $this->namespace;
	}

	/**
	 * Render the exception to a response.
	 *
	 * @param  \Foundation\Http\Request $request
	 * @param  \Exception $e
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function renderException( $request, $e )
	{
		try
		{
			return $this->bindings[ExceptionHandler::class]->render( $request, $e );
		}
		catch ( \Exception $ee )
		{
			$e = FlattenException::create( $e );
			$handler = new SymfonyExceptionHandler( true );
			$content = $handler->getContent( $e );

			$decorated = <<<EOF
<!DOCTYPE html>
<html xmlns=\"http://www.w3.org/1999/xhtml\">
	<head>
		<meta name="robots" content="noindex,nofollow" />
		<meta charset=\"utf-8\">
		<title>Penoaks Framework Exception</title>
		<!-- Stylesheets -->
		<link rel=\"stylesheet\" type=\"text/css\" href=\"http://fonts.googleapis.com/css?family=Prosto+One\" />
		<!-- Scripts -->
		<script src="https://code.jquery.com/jquery-3.0.0.min.js"></script>
		<style type="text/css">
		/*<![CDATA[*/
		html,body,div,span,applet,object,iframe,h1,h2,h3,h4,h5,h6,p,blockquote,pre,a,abbr,acronym,address,big,cite,code,del,dfn,em,font,img,ins,kbd,q,s,samp,small,strike,strong,sub,sup,tt,var,b,u,i,center,dl,dt,dd,ol,ul,li,fieldset,form,label,legend,table,caption,tbody,tfoot,thead,tr,th,td{border:0;outline:0;font-size:100%;vertical-align:baseline;background:transparent;margin:0;padding:0;}
		body{line-height:1;}
		ol,ul{list-style:none;}
		blockquote,q{quotes:none;}
		blockquote:before,blockquote:after,q:before,q:after{content:none;}
		:focus{outline:0;}
		ins{text-decoration:none;}
		del{text-decoration:line-through;}
		table{border-collapse:collapse;border-spacing:0;}
		
		body {
			font: normal 9pt Sans;
			color: #000;
			background: #ddd;
		}
		
		h1 {
			font: normal 18pt Sans;
			color: #f00;
			margin-bottom: .5em;
		}
		
		h2 {
			font: normal 14pt Sans;
			color: #800000;
			margin-bottom: .5em;
		}
		
		h3 {
			font: bold 11pt Sans;
		}
		
		pre {
			font: normal 11pt Menlo, Consolas, "Lucida Console", Monospace;
		}
		
		pre span.error {
			display: block;
			background: #fce3e3;
		}
		
		pre span.ln {
			color: #999;
			padding-right: 0.5em;
			margin-left: -46px;
		}
		
		pre span.error-ln {
			font-weight: bold;
		}
		
		.code pre {
			background-color: #ffe;
			line-height: 125%;
			margin: 0.5em 0 0.5em 46px;
			padding: 0.5em;
			border: 1px solid #eee;
			border-left: 1px solid #ccc;
			white-space: pre-wrap;
		}
		
		.container {
			width: 1200px;
			margin: 0 auto;
			padding: 32px;
			background-color: #fff;
		}
		
		.version {
			color: gray;
			font-size: 8pt;
			border-top: 1px solid #aaa;
			padding-top: 1em;
			margin-bottom: 1em;
		}
		
		.message {
			color: #000;
			padding: 1em;
			font-size: 11pt;
			background: #f3f3f3;
			-webkit-border-radius: 10px;
			-moz-border-radius: 10px;
			border-radius: 10px;
			margin-bottom: 1em;
			line-height: 160%;
			white-space: pre-wrap;
		}
		
		.source {
			margin-bottom: 1em;
		}
		
		.source .file {
			margin-bottom: 1em;
		}
		
		.traces {
			margin: 2em 0;
		}
		
		.trace {
			margin: 0.5em 0;
			padding: 0.5em;
		}
		
		.trace.groovy {
			border: 1px dashed #6398aa;
		}
		
		.trace.app {
			border: 1px dashed #c00;
		}
		
		.trace .number {
			text-align: right;
			width: 2em;
			padding: 0.5em;
		}
		
		.trace .content {
			padding: 0.5em;
		}
		
		.trace .plus,
		.trace .minus {
			display:inline;
			vertical-align:middle;
			text-align:center;
			border:1px solid #000;
			color:#000;
			font-size:10px;
			line-height:10px;
			margin:0;
			padding:0 1px;
			width:10px;
			height:10px;
		}
		
		.trace.collapsed .minus,
		.trace.expanded .plus,
		.trace.collapsed pre {
			display: none;
		}
		
		.trace-file {
			cursor: pointer;
			padding: 0.2em;
		}
		
		.trace-file:hover {
			background: #F3A4CF;
		}
		/*]]>*/
		</style>
	</head>
	<body>
		<div class="container">
		$content
		</div>
		
		<script type="text/javascript">
		/*<![CDATA[*/
		var traceReg = new RegExp("(^|\\s)trace-file(\\s|\$)");
		var collapsedReg = new RegExp("(^|\\s)collapsed(\\s|\$)");
		
		var e = document.getElementsByTagName("div");
		for(var j=0,len=e.length;j<len;j++){
			if(traceReg.test(e[j].className)){
				e[j].onclick = function(){
					var trace = this.parentNode.parentNode;
					if(collapsedReg.test(trace.className))
						trace.className = trace.className.replace("collapsed", "expanded");
					else
						trace.className = trace.className.replace("expanded", "collapsed");
				}
			}
		}
		/*]]>*/
		</script>
	</body>
</html>
EOF;

			return SymfonyResponse::create( $decorated, $e->getStatusCode(), $e->getHeaders() );
		}
	}

	/**
	 * Report the exception to the exception handler.
	 *
	 * @param  \Exception $e
	 * @return void
	 */
	public function reportException( $e )
	{
		try
		{
			$this->bindings[ExceptionHandler::class]->report( $e );
		}
		catch ( \Exception $e )
		{
			// Ignore Reporting Failures!
		}
	}

	public function join()
	{
		$response = $this->kernel->handle( $request = Request::capture() );
		$response->send();
		$this->kernel->terminate( $request, $response );
	}
}
