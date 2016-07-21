<?php
namespace Penoaks;

use Error;
use ErrorException;
use Illuminate\Encryption\Encrypter;
use Illuminate\Encryption\McryptEncrypter;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Hashing\HashServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Pipeline;
use Illuminate\Support\Composer;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Str;
use Milky\Http\Cookies\CookieJar;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Penoaks\Barebones\DynamicArray;
use Penoaks\Bindings\Bindings;
use Penoaks\Bootstrap\ConfigureLogging;
use Penoaks\Bootstrap\LoadConfiguration;
use Penoaks\Cookie\CookieJar;
use Penoaks\Events\Dispatcher;
use Penoaks\Facades\Config;
use Penoaks\Facades\Request as RequestFacade;
use Penoaks\Providers\ProviderRepository;
use Milky\Http\Routing\Router;
use Penoaks\Routing\UrlGenerator;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Zend\Diactoros\Response as PsrResponse;


/**
 * The MIT License (MIT)
 * Copyright 2016 HolyWorlds Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class Framework
{
	/**
	 * Indicates if the class aliases have been registered.
	 *
	 * @var bool
	 */
	protected static $aliasesRegistered = false;

	/**
	 * The base path of the application installation.
	 *
	 * @var string
	 */
	protected $basePath;

	/**
	 * @var Framework
	 */
	public static $selfInstance;

	/**
	 * Stores the Service Bindings instance.
	 *
	 * @var Bindings
	 */
	public $bindings;

	/**
	 * @var array
	 */
	private $middleware = [];

	/**
	 * Holds the registered list of commands
	 *
	 * @var DynamicArray
	 */
	public $commands;

	/**
	 * Holds the registered list of service providers
	 *
	 * @var ProviderRepository
	 */
	public $providers;

	/**
	 * All of the loaded configuration files.
	 *
	 * @var array
	 */
	protected $loadedConfigurations = [];

	/**
	 * The loaded service providers.
	 *
	 * @var array
	 */
	protected $loadedProviders = [];

	/**
	 * The service binding methods that have been executed.
	 *
	 * @var array
	 */
	protected $ranServiceBinders = [];

	/**
	 * A custom callback used to configure Monolog.
	 *
	 * @var callable|null
	 */
	protected $monologConfigurator;

	/**
	 * The application namespace.
	 *
	 * @var string
	 */
	protected $namespace;

	/**
	 * @var array
	 */
	protected $bootedCallbacks = [];

	/**
	 * Create a new Lumen application instance.
	 *
	 * @param  string|null $basePath
	 * @return void
	 */
	public function __construct( $loader, $basePath = null )
	{
		$this->commands = new DynamicArray();
		$this->providers = new ProviderRepository();
		$this->bindings = new Bindings( $this );

		$this->bindings->instance( 'app', new FakeApp( $this ) );

		$this->basePath = $basePath;

		$this->registerBinders();

		( new LoadConfiguration() )->boot( $this );
		( new ConfigureLogging() )->boot( $this );

		$this->bootstrapContainer();
		$this->registerErrorHandling();

		$this->bindings->instance( 'loader', $loader );

		$this->bindings->make( 'db' );
	}

	public function addMiddleware( array $middleware )
	{
		$this->middleware = array_merge( $this->middleware, $middleware );
	}

	/**
	 * @param string $key
	 * @param array $middleware
	 */
	public function addMiddlewareGroup( $key, array $middleware )
	{
		$this->router()->middlewareGroup( $key, $middleware );
	}

	/**
	 * @param string $key
	 * @param array $middleware
	 */
	public function addRouteMiddleware( $key, array $middleware )
	{
		$this->router()->middleware( $key, $middleware );
	}

	protected function registerBinders()
	{
		$b = &$this->bindings;

		$b->addBinder( 'registerAuthBindings', [
			'auth',
			'auth.driver',
			'Illuminate\Contracts\Auth\Guard',
			'Illuminate\Contracts\Auth\Access\Gate'
		], [$this, 'registerAuthBindings'] );
		$b->addBinder( 'registerBroadcastingBindings', ['Illuminate\Contracts\Broadcasting\Broadcaster'], [
			$this,
			'registerBroadcastingBindings'
		] );
		$b->addBinder( 'registerBusBindings', ['Illuminate\Contracts\Bus\Dispatcher'], [$this, 'registerBusBindings'] );
		$b->addBinder( 'registerCacheBindings', [
			'cache',
			'cache.store',
			'Illuminate\Contracts\Cache\Factory',
			'Illuminate\Contracts\Cache\Repository'
		], [$this, 'registerCacheBindings'] );
		$b->addBinder( 'registerComposerBindings', ['composer'], [$this, 'registerComposerBindings'] );
		$b->addBinder( 'registerDatabaseBindings', ['db', 'Illuminate\Database\Eloquent\Factory'], [
			$this,
			'registerDatabaseBindings'
		] );
		$b->addBinder( 'registerEncrypterBindings', ['encrypter', 'Illuminate\Contracts\Encryption\Encrypter'], [
			$this,
			'registerEncrypterBindings'
		] );
		$b->addBinder( 'registerEventBindings', ['events'], [
			$this,
			'registerEventBindings'
		] );
		$b->addBinder( 'registerFilesBindings', ['files'], [$this, 'registerFilesBindings'] );
		$b->addBinder( 'registerHashBindings', ['hash', 'Illuminate\Contracts\Hashing\Hasher'], [
			$this,
			'registerHashBindings'
		] );
		$b->addBinder( 'registerQueueBindings', [
			'queue',
			'queue.connection',
			'Illuminate\Contracts\Queue\Factory',
			'Illuminate\Contracts\Queue\Queue'
		], [$this, 'registerQueueBindings'] );
		$b->addBinder( 'registerRequestBindings', ['request', 'Illuminate\Http\Request'], [
			$this,
			'registerRequestBindings'
		] );
		$b->addBinder( 'registerPsrRequestBindings', ['Psr\Http\Message\ServerRequestInterface'], [
			$this,
			'registerPsrRequestBindings'
		] );
		$b->addBinder( 'registerPsrResponseBindings', ['Psr\Http\Message\ResponseInterface'], [
			$this,
			'registerPsrResponseBindings'
		] );
		$b->addBinder( 'registerTranslationBindings', ['translator'], [$this, 'registerTranslationBindings'] );
		$b->addBinder( 'registerValidatorBindings', ['validator', 'Illuminate\Contracts\Validation\Factory'], [
			$this,
			'registerValidatorBindings'
		] );
		$b->addBinder( 'registerViewBindings', ['view', 'Illuminate\Contracts\View\Factory'], [
			$this,
			'registerViewBindings'
		] );
		$b->addBinder( 'registerCookieBindings', ['cookie', 'Illuminate\Contracts\Cookie\QueueingFactory'], [
			$this,
			'registerCookieBindings'
		] );
		$b->addBinder( 'registerSessionBindings', ['session', 'session.store'], [$this, 'registerSessionBindings'] );
		$b->addBinder( 'registerRouteBindings', ['router', 'url'], [$this, 'registerRouteBindings'] );
	}

	/**
	 * Boot the application's service providers.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->providers->bootProviders();

		foreach ( $this->bootedCallbacks as $callback )
			call_user_func( $callback, $this );
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
	}

	/**
	 * @param Request $request
	 *
	 * @return Response
	 */
	public function handleHttp()
	{
		$request = Request::createFromBase( $this->bindings->make( 'request' ) );

		try
		{
			$request->enableHttpMethodParameterOverride();

			$this->bindings->instance( 'request', $request );
			Facade::clearResolvedInstance( 'request' );
			RequestFacade::__reset();

			$response = ( new Pipeline( $this->bindings ) )->send( $request )->through( $this->middleware )->then( function ( $request )
			{
				$this->bindings->instance( 'request', $request );

				return $this->router()->dispatch( $request );
			} );
		}
		catch ( \Exception $e )
		{
			return $this->sendExceptionToHandler( $e );
		}
		catch ( \Throwable $e )
		{
			return $this->sendExceptionToHandler( $e );
		}

		$this->bindings['events']->fire( 'kernel.handled', [$request, $response] );

		$this->bindings->instance( 'response', $response );

		return $response;
	}

	/**
	 * Call the terminate method on any terminable middleware.
	 *
	 * @param  Request $request
	 * @param  Response $response
	 * @return void
	 */
	public function terminate( $request = null, $response = null )
	{
		if ( is_null( $request ) )
			$request = $this->request();
		if ( is_null( $response ) )
			$response = $this->response();

		$finalMiddleware = /*$this->shouldSkipMiddleware() ? [] : */
			array_merge( $this->gatherRouteMiddlewares( $request ), $this->middleware );
		foreach ( $finalMiddleware as $middleware )
		{
			list( $name, $parameters ) = $this->parseMiddleware( $middleware );
			$instance = $this->bindings->make( $name );
			if ( method_exists( $instance, 'terminate' ) )
				$instance->terminate( $request, $response );
		}

		/*foreach ($this->terminatingCallbacks as $terminating)
			$this->call($terminating); */
	}

	/**
	 * @return Request
	 */
	public function request()
	{
		return $this->bindings->make( 'request' );
	}

	/**
	 * @return Response
	 */
	public function response()
	{
		return $this->bindings->make( 'response' );
	}

	/**
	 * Gather the route middleware for the given request.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @return array
	 */
	protected function gatherRouteMiddlewares( $request )
	{
		if ( $route = $request->route() )
			return $this->router()->gatherRouteMiddlewares( $route );

		return [];
	}

	/**
	 * Get router instance
	 *
	 * @return Router
	 */
	public function router()
	{
		return $this->bindings['router'];
	}

	/**
	 * Parse a middleware string to get the name and parameters.
	 *
	 * @param  string $middleware
	 * @return array
	 */
	protected function parseMiddleware( $middleware )
	{
		list( $name, $parameters ) = array_pad( explode( ':', $middleware, 2 ), 2, [] );
		if ( is_string( $parameters ) )
			$parameters = explode( ',', $parameters );

		return [$name, $parameters];
	}

	/**
	 * Bootstrap the application container.
	 *
	 * @return void
	 */
	protected function bootstrapContainer()
	{
		static::$selfInstance = $this;

		$this->bindings->instance( 'path', $this->path() );

		$this->registerContainerAliases();
	}

	/**
	 * Get the version number of the application.
	 *
	 * @return string
	 */
	public function version()
	{
		return 'Lumen (5.2.7) (Laravel Components 5.2.*)';
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
		$env = Config::get( 'app.env', 'production' );

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

	/**
	 * Register container bindings for the application.
	 *
	 * @return void
	 */
	public function registerAuthBindings()
	{
		$this->bindings->singleton( 'auth', function ()
		{
			return $this->loadComponent( 'auth', 'Illuminate\Auth\AuthServiceProvider', 'auth' );
		} );

		$this->bindings->singleton( 'auth.driver', function ()
		{
			return $this->loadComponent( 'auth', 'Illuminate\Auth\AuthServiceProvider', 'auth.driver' );
		} );

		$this->bindings->singleton( 'Illuminate\Contracts\Auth\Access\Gate', function ()
		{
			return $this->loadComponent( 'auth', 'Illuminate\Auth\AuthServiceProvider', 'Illuminate\Contracts\Auth\Access\Gate' );
		} );
	}

	/**
	 * Register container bindings for the application.
	 *
	 * @return void
	 */
	public function registerBroadcastingBindings()
	{
		$this->bindings->singleton( 'Illuminate\Contracts\Broadcasting\Broadcaster', function ()
		{
			$this->configure( 'broadcasting' );

			$this->providers->add( 'Illuminate\Broadcasting\BroadcastServiceProvider' );

			return $this->bindings->make( 'Illuminate\Contracts\Broadcasting\Broadcaster' );
		} );
	}

	/**
	 * Register container bindings for the application.
	 *
	 * @return void
	 */
	public function registerBusBindings()
	{
		$this->bindings->singleton( 'Illuminate\Contracts\Bus\Dispatcher', function ()
		{
			$this->providers->add( 'Illuminate\Bus\BusServiceProvider' );

			return $this->bindings->make( 'Illuminate\Contracts\Bus\Dispatcher' );
		} );
	}

	/**
	 * Register container bindings for the application.
	 *
	 * @return void
	 */
	public function registerCacheBindings()
	{
		$this->bindings->singleton( 'cache', function ()
		{
			return $this->loadComponent( 'cache', 'Illuminate\Cache\CacheServiceProvider' );
		} );
		$this->bindings->singleton( 'cache.store', function ()
		{
			return $this->loadComponent( 'cache', 'Illuminate\Cache\CacheServiceProvider', 'cache.store' );
		} );
	}

	/**
	 * Register container bindings for the application.
	 *
	 * @return void
	 */
	public function registerComposerBindings()
	{
		$this->bindings->singleton( 'composer', function ( $fw )
		{
			return new Composer( $fw->bindings->make( 'files' ), $this->basePath() );
		} );
	}

	/**
	 * Register session bindings for the application
	 */
	public function registerSessionBindings()
	{
		$this->providers->add( 'Illuminate\Session\SessionServiceProvider' );
	}

	/**
	 * Register cookie bindings for the application
	 */
	public function registerCookieBindings()
	{
		$this->bindings->singleton( 'cookie', function ( $bindings )
		{
			$config = $bindings['config']['session'];
			return ( new CookieJar )->setDefaultPathAndDomain( $config['path'], $config['domain'], $config['secure'] );
		} );
	}

	/**
	 * Register container bindings for the application.
	 */
	public function registerDatabaseBindings()
	{
		$this->bindings->singleton( 'db', function ()
		{
			return $this->loadComponent( 'database', [
				'Illuminate\Database\DatabaseServiceProvider',
				'Illuminate\Pagination\PaginationServiceProvider',
			], 'db' );
		} );
	}

	/**
	 * Register container bindings for the application.
	 *
	 * @return void
	 */
	public function registerEncrypterBindings()
	{
		$this->bindings->singleton( 'encrypter', function ()
		{
			$config = $this->bindings->make( 'config' )->get( 'app' );

			if ( Str::startsWith( $key = $config['key'], 'base64:' ) )
				$key = base64_decode( substr( $key, 7 ) );

			return $this->getEncrypterForKeyAndCipher( $key, $config['cipher'] );
		} );
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
		elseif ( McryptEncrypter::supported( $key, $cipher ) )
			return new McryptEncrypter( $key, $cipher );
		else
			throw new \RuntimeException( 'No supported encrypter found. The cipher and / or key length are invalid.' );
	}

	/**
	 * Register container bindings for the application.
	 *
	 * @return void
	 */
	public function registerEventBindings()
	{
		$this->bindings->protect( 'events' );
		$this->bindings->instance( 'events', ( new Dispatcher( $this->bindings ) )->setQueueResolver( function ()
		{
			return $this->bindings->make( 'Illuminate\Contracts\Queue\Factory' );
		} ) );
	}

	/**
	 * Register container bindings for the application.
	 *
	 * @return void
	 */
	public function registerFilesBindings()
	{
		$this->bindings->singleton( 'files', function ()
		{
			return new Filesystem;
		} );
	}

	/**
	 * Register container bindings for the application.
	 *
	 * @return void
	 */
	public function registerHashBindings()
	{
		$this->bindings->singleton( 'hash', function ()
		{
			$this->providers->add( new HashServiceProvider( $this->bindings->make( 'app' ) ) );

			return $this->bindings->make( 'hash' );
		} );
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
	 * Register container bindings for the application.
	 *
	 * @return void
	 */
	public function registerQueueBindings()
	{
		$this->bindings->singleton( 'queue', function ()
		{
			return $this->loadComponent( 'queue', 'Illuminate\Queue\QueueServiceProvider', 'queue' );
		} );
		$this->bindings->singleton( 'queue.connection', function ()
		{
			return $this->loadComponent( 'queue', 'Illuminate\Queue\QueueServiceProvider', 'queue.connection' );
		} );
	}

	/**
	 * Get the Monolog handler for the application.
	 *
	 * @return \Monolog\Handler\AbstractHandler
	 */
	protected function getMonologHandler()
	{
		return ( new StreamHandler( $this->buildPath( 'logs/fw.log', 'storage' ), Logger::DEBUG ) )->setFormatter( new LineFormatter( null, null, true, true ) );
	}

	/**
	 * Register container bindings for the application.
	 *
	 * @return void
	 */
	public function registerRequestBindings()
	{
		$this->bindings->singleton( 'request', function ()
		{
			return $this->prepareRequest( Request::capture() );
		} );
	}

	/**
	 * Prepare the given request instance for use with the application.
	 *
	 * @param   \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\Request
	 */
	protected function prepareRequest( Request $request )
	{
		$request->setUserResolver( function ()
		{
			return $this->bindings->make( 'auth' )->user();
		} )->setRouteResolver( function ()
		{
			return $this->currentRoute;
		} );

		return $request;
	}

	/**
	 * Register container bindings for the PSR-7 request implementation.
	 *
	 * @return void
	 */
	public function registerPsrRequestBindings()
	{
		$this->bindings->singleton( 'Psr\Http\Message\ServerRequestInterface', function ()
		{
			return ( new DiactorosFactory )->createRequest( $this->bindings->make( 'request' ) );
		} );
	}

	/**
	 * Register container bindings for the PSR-7 response implementation.
	 *
	 * @return void
	 */
	public function registerPsrResponseBindings()
	{
		$this->bindings->singleton( 'Psr\Http\Message\ResponseInterface', function ()
		{
			return new PsrResponse();
		} );
	}

	/**
	 * Register container bindings for the application.
	 *
	 * @return void
	 */
	public function registerTranslationBindings()
	{
		$this->bindings->singleton( 'translator', function ()
		{
			$this->configure( 'src' );

			$this->bindings->instance( 'path.lang', $this->getLanguagePath() );

			$this->providers->add( 'Illuminate\Translation\TranslationServiceProvider' );

			return $this->bindings->make( 'translator' );
		} );
	}

	/**
	 * Get the path to the application's language files.
	 *
	 * @return string
	 */
	protected function getLanguagePath()
	{
		if ( is_dir( $langPath = $this->basePath() . '/resources/lang' ) )
		{
			return $langPath;
		}
		else
		{
			return __DIR__ . '/../resources/lang';
		}
	}

	/**
	 * Register container bindings for the application.
	 *
	 * @return void
	 */
	public function registerRouteBindings( Request $request )
	{
		$router = new Router( $this->bindings->make( 'events' ), $this->bindings );

		$this->bindings->instance( 'router', $router );
		$this->bindings->instance( 'url', new UrlGenerator( $router->getRoutes(), $request ) );
	}

	/**
	 * Register container bindings for the application.
	 *
	 * @return void
	 */
	public function registerValidatorBindings()
	{
		$this->bindings->singleton( 'validator', function ()
		{
			$this->providers->add( 'Illuminate\Validation\ValidationServiceProvider' );

			return $this->bindings->make( 'validator' );
		} );
	}

	/**
	 * Register container bindings for the application.
	 *
	 * @return void
	 */
	public function registerViewBindings()
	{
		$this->bindings->singleton( 'view', function ()
		{
			return $this->loadComponent( 'view', 'Illuminate\View\ViewServiceProvider' );
		} );
	}

	/**
	 * Configure and load the given component and provider.
	 *
	 * @param  string $config
	 * @param  array|string $providers
	 * @param  string|null $return
	 * @return mixed
	 */
	public function loadComponent( $config, $providers, $return = null )
	{
		$this->configure( $config );

		foreach ( (array) $providers as $provider )
			$this->providers->add( $provider );

		return $this->bindings->make( $return ?: $config );
	}

	/**
	 * Load a configuration file into the application.
	 *
	 * @param  string $name
	 * @return void
	 */
	public function configure( $name )
	{
		if ( isset( $this->loadedConfigurations[$name] ) )
		{
			return;
		}

		$this->loadedConfigurations[$name] = true;

		$path = $this->getConfigurationPath( $name );

		if ( $path )
		{
			$this->bindings->make( 'config' )->set( $name, require $path );
		}
	}

	/**
	 * Get the path to the given configuration file.
	 *
	 * If no name is provided, then we'll return the path to the config folder.
	 *
	 * @param  string|null $name
	 * @return string
	 */
	public function getConfigurationPath( $name = null )
	{
		if ( !$name )
		{
			$appConfigDir = $this->basePath( 'config' ) . '/';

			if ( file_exists( $appConfigDir ) )
			{
				return $appConfigDir;
			}
			elseif ( file_exists( $path = __DIR__ . '/../config/' ) )
			{
				return $path;
			}
		}
		else
		{
			$appConfigPath = $this->basePath( 'config' ) . '/' . $name . '.php';

			if ( file_exists( $appConfigPath ) )
			{
				return $appConfigPath;
			}
			elseif ( file_exists( $path = __DIR__ . '/../config/' . $name . '.php' ) )
			{
				return $path;
			}
		}
	}

	/**
	 * Register the facades for the application.
	 *
	 * @return void
	 */
	public function withFacades()
	{
		/** @noinspection PhpParamsInspection */
		Facade::setFacadeApplication( $this->bindings );

		if ( !static::$aliasesRegistered )
		{
			static::$aliasesRegistered = true;

			/**
			 * Laravel Facades
			 */
			class_alias( Blade::class, 'Blade' );

			/*
			class_alias( 'Penoaks\Facades\Auth', 'Auth' );
			class_alias( 'Penoaks\Facades\Cache', 'Cache' );
			class_alias( 'Penoaks\Facades\DB', 'DB' );
			class_alias( 'Penoaks\Facades\Event', 'Event' );
			class_alias( 'Penoaks\Facades\Gate', 'Gate' );
			class_alias( 'Penoaks\Facades\Logging', 'Logging' );
			class_alias( 'Penoaks\Facades\Queue', 'Queue' );
			class_alias( 'Penoaks\Facades\Schema', 'Schema' );
			class_alias( 'Penoaks\Facades\URL', 'URL' );
			class_alias( 'Penoaks\Facades\Validator', 'Validator' );
			*/
		}
	}

	/**
	 * Get the path to the application "src" directory.
	 *
	 * @return string
	 */
	public function path()
	{
		return $this->basePath . DIRECTORY_SEPARATOR . 'src';
	}

	/**
	 * Get the base path for the application.
	 *
	 * @param  string|null $path
	 * @return string
	 */
	public function basePath( $path = null )
	{
		if ( isset( $this->basePath ) )
			return $this->basePath . ( $path ? '/' . $path : $path );

		if ( $this->runningInConsole() )
			$this->basePath = getcwd();
		else
			$this->basePath = realpath( getcwd() . '/../' );

		return $this->basePath( $path );
	}

	/**
	 * Determine if the application is running in the console.
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
		return $this->environment() == 'testing';
	}

	/**
	 * Prepare the application to execute a console command.
	 *
	 * @return void
	 */
	public function prepareForConsoleCommand()
	{
		$this->withFacades();

		$this->bindings->make( 'cache' );
		$this->bindings->make( 'queue' );

		$this->configure( 'database' );

		$this->providers->add( 'Illuminate\Database\MigrationServiceProvider' );
		$this->providers->add( 'Illuminate\Database\SeedServiceProvider' );
		$this->providers->add( 'Illuminate\Queue\ConsoleServiceProvider' );
	}

	/**
	 * Register the core container aliases.
	 *
	 * @return void
	 */
	public function registerContainerAliases()
	{
		$aliases = [
			'fw' => [
				'Penoaks\Framework'
			],
			'app' => [
				'Illuminate\Foundation\Application',
				'Illuminate\Contracts\Foundation\Application'
			],
			'bindings' => [
				'Penoaks\Bindings',
				'Illuminate\Container\Container',
				'Illuminate\Contracts\Container\Container'
			],
			'auth' => [
				'Illuminate\Auth\AuthManager',
				'Illuminate\Contracts\Auth\Factory'
			],
			'auth.driver' => [
				'Illuminate\Contracts\Auth\Guard'],
			'blade.compiler' => [
				'Illuminate\View\Compilers\BladeCompiler'],
			'cache' => [
				'Illuminate\Cache\CacheManager',
				'Illuminate\Contracts\Cache\Factory'],
			'cache.store' => [
				'Illuminate\Cache\Repository',
				'Illuminate\Contracts\Cache\Repository'],
			'config' => [
				'Illuminate\Config\Repository',
				'Illuminate\Contracts\Config\Repository'],
			'cookie' => [
				'Illuminate\Cookie\CookieJar',
				'Illuminate\Contracts\Cookie\Factory',
				'Illuminate\Contracts\Cookie\QueueingFactory'
			],
			'encrypter' => [
				'Illuminate\Encryption\Encrypter',
				'Illuminate\Contracts\Encryption\Encrypter'],
			'db' => [
				'Illuminate\Database\DatabaseManager',
				'Illuminate\Database\ConnectionResolverInterface'],
			'db.connection' => [
				'Illuminate\Database\Connection',
				'Illuminate\Database\ConnectionInterface'], //
			'events' => [
				'Penoaks\Events\Dispatcher',
				'Illuminate\Events\Dispatcher',
				'Illuminate\Contracts\Events\Dispatcher'],
			'files' => [
				'Illuminate\Filesystem\Filesystem'],
			'filesystem' => [
				'Illuminate\Filesystem\FilesystemManager',
				'Illuminate\Contracts\Filesystem\Factory'],
			'filesystem.disk' => [
				'Illuminate\Contracts\Filesystem\Filesystem'],
			'filesystem.cloud' => [
				'Illuminate\Contracts\Filesystem\Cloud'],
			'hash' => [
				'Illuminate\Contracts\Hashing\Hasher'],
			'translator' => [
				'Illuminate\Translation\Translator',
				'Symfony\Component\Translation\TranslatorInterface'],
			'log' => [
				'Illuminate\Log\Writer',
				'Illuminate\Contracts\Logging\Log',
				'Psr\Log\LoggerInterface'],
			'mailer' => [
				'Illuminate\Mail\Mailer',
				'Illuminate\Contracts\Mail\Mailer',
				'Illuminate\Contracts\Mail\MailQueue'
			],
			'auth.password' => [
				'Illuminate\Auth\Passwords\PasswordBrokerManager',
				'Illuminate\Contracts\Auth\PasswordBrokerFactory'
			],
			'auth.password.broker' => [
				'Illuminate\Auth\Passwords\PasswordBroker',
				'Illuminate\Contracts\Auth\PasswordBroker'
			],
			'queue' => [
				'Illuminate\Queue\QueueManager',
				'Illuminate\Contracts\Queue\Factory',
				'Illuminate\Contracts\Queue\Monitor'
			],
			'queue.connection' => [
				'Illuminate\Contracts\Queue\Queue'],
			'queue.failer' => [
				'Illuminate\Queue\Failed\FailedJobProviderInterface'],
			'redirect' => [
				'Illuminate\Routing\Redirector'],
			'redis' => [
				'Illuminate\Redis\Database',
				'Illuminate\Contracts\Redis\Database'],
			'request' => [
				'Illuminate\Http\Request',
				'Symfony\Component\HttpFoundation\Request'],
			'router' => [
				'Milky\Http\Routing\Router',
				'Milky\Http\Routing\Router',
				'Illuminate\Contracts\Routing\Registrar'],
			'session' => [
				'Illuminate\Session\SessionManager'],
			'session.store' => [
				'Illuminate\Session\Store',
				'Symfony\Component\HttpFoundation\Session\SessionInterface'
			],
			'url' => [
				'Illuminate\Routing\UrlGenerator',
				'Illuminate\Contracts\Routing\UrlGenerator'],
			'validator' => [
				'Illuminate\Validation\Factory',
				'Illuminate\Contracts\Validation\Factory'],
			'view' => [
				'Illuminate\View\Factory',
				'Illuminate\Contracts\View\Factory'],
			'exceptionHandler' => [
				'Illuminate\Contracts\Debug\ExceptionHandler',
				'Laravel\Lumen\Exceptions\Handler'
			],
			'consoleKernel' => [
				'Illuminate\Contracts\Console\Kernel',
			],
		];

		foreach ( $aliases as $key => $alias )
			$this->bindings->alias( $key, $alias );
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
			throw new NotFoundHttpException( $message );

		throw new HttpException( $code, $message, null, $headers );
	}

	/**
	 * Set the error handling for the application.
	 *
	 * @return void
	 */
	public function registerErrorHandling()
	{
		error_reporting( -1 );

		set_error_handler( function ( $level, $message, $file = '', $line = 0 )
		{
			if ( error_reporting() & $level )
				throw new ErrorException( $message, 0, $level, $file, $line );
		} );

		set_exception_handler( function ( $e )
		{
			$this->handleUncaughtException( $e );
		} );

		register_shutdown_function( function ()
		{
			$this->handleShutdown();
		} );
	}

	/**
	 * Handle the application shutdown routine.
	 *
	 * @return void
	 */
	protected function handleShutdown()
	{
		if ( !is_null( $error = error_get_last() ) && $this->isFatalError( $error['type'] ) )
			$this->handleUncaughtException( new FatalErrorException( $error['message'], $error['type'], 0, $error['file'], $error['line'] ) );
	}

	/**
	 * Determine if the error type is fatal.
	 *
	 * @param  int $type
	 * @return bool
	 */
	protected function isFatalError( $type )
	{
		$errorCodes = [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE];

		if ( defined( 'FATAL_ERROR' ) )
			$errorCodes[] = FATAL_ERROR;

		return in_array( $type, $errorCodes );
	}

	/**
	 * Send the exception to the handler and return the response.
	 *
	 * @param  \Throwable $e
	 * @return Response
	 */
	protected function sendExceptionToHandler( $e )
	{
		if ( !$this->bindings->bound( 'exceptionHandler' ) )
			throw $e;

		$handler = $this->resolveExceptionHandler();

		if ( $e instanceof Error )
			$e = new FatalThrowableError( $e );

		$handler->report( $e );

		return $handler->render( $this->bindings->make( 'request' ), $e );
	}

	/**
	 * Handle an uncaught exception instance.
	 *
	 * @param  \Throwable $e
	 * @return void
	 */
	protected function handleUncaughtException( $e )
	{
		if ( !$this->bindings->bound( 'exceptionHandler' ) )
			throw $e;

		$handler = $this->resolveExceptionHandler();

		if ( $e instanceof Error )
			$e = new FatalThrowableError( $e );

		$handler->report( $e );

		if ( $this->runningInConsole() )
			$handler->renderForConsole( new ConsoleOutput, $e );
		else
			$handler->render( $this->bindings->make( 'request' ), $e )->send();
	}

	/**
	 * Get the exception handler from the container.
	 *
	 * @return mixed
	 */
	protected function resolveExceptionHandler()
	{
		return $this->bindings->make( 'exceptionHandler' );
	}

	public function buildPath( $slug, $location = null )
	{
		if ( is_null( $location ) )
		{
			$location = $slug;
			$slug = null;
		}

		if ( !empty( $slug ) )
			$slug = '/' . $slug;

		// TODO Load paths from config

		switch ( $location )
		{
			case "src":
				return $this->basePath . '/src' . $slug;
			case "database":
				return $this->basePath . '/fw' . $slug;
			case "logs":
				return $this->basePath . '/fw/logs' . $slug;
			case "lang":
				return $this->basePath . '/src/lang' . $slug;
			case "storage":
			case "fw":
				return $this->basePath . '/fw' . $slug;
			case "public":
			case "base":
			default: // base
				return $this->basePath . $slug;
		}
	}
}
