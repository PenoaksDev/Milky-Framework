<?php
namespace Penoaks;

use ArrayAccess;
use Closure;
use Illuminate\Contracts\Container\Container;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * The MIT License (MIT)
 * Copyright 2016 Penoaks Publishing Co. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class FakeApp implements HttpKernelInterface, ArrayAccess, Container
{
	const VERSION = '5.2';

	public $fw;

	public $bindings;

	public function __construct( Framework $fw )
	{
		$this->fw = $fw;
		$this->bindings = $fw->bindings;
	}

	public function version()
	{
		return $this->fw->version();
	}

	/*
	public function bootstrapWith( array $bootstrappers )
	{
		$this->fw->bootstrapWith( $bootstrappers );
	}

	public function afterLoadingEnvironment( Closure $callback )
	{
		$this->fw->afterLoadingEnvironment( $callback );
	}

	public function beforeBootstrapping( $bootstrapper, Closure $callback )
	{
		$this->fw->beforeBootstrapping( $bootstrapper, $callback );
	}

	public function afterBootstrapping( $bootstrapper, Closure $callback )
	{
		$this->fw->afterBootstrapping( $bootstrapper, $callback );
	}

	public function hasBeenBootstrapped()
	{
		$this->fw->hasBeenBootstrapped();
	}
	*/

	public function path()
	{
		$this->fw->path();
	}

	public function basePath( $path = null )
	{
		$this->fw->basePath( $path );
	}

	/*
	public function bootstrapPath()
	{
		$this->fw->bootstrapPath();
	}
	*/

	public function configPath( $append = null )
	{
		$this->fw->buildPath( $append, 'config' );
	}

	public function databasePath( $append = null )
	{
		$this->fw->buildPath( $append, 'database' );
	}

	public function useDatabasePath( $path )
	{
		// FUTURE USE!
	}

	public function langPath( $append = null )
	{
		$this->fw->buildPath( $append, 'lang' );
	}

	public function publicPath( $append = null )
	{
		$this->fw->buildPath( $append, 'public' );
	}

	public function storagePath( $append = null )
	{
		$this->fw->buildPath( $append, 'storage' );
	}

	public function useStoragePath( $path )
	{
		// FUTURE USE!
	}

	/*
	public function environmentPath()
	{
		$this->fw->environmentPath();
	}

	public function useEnvironmentPath( $path )
	{
		// FUTURE USE!
	}

	public function loadEnvironmentFrom( $file )
	{
		$this->fw->loadEnvironmentFrom( $file );
	}

	public function environmentFile()
	{
		$this->fw->environmentFile();
	}

	public function environmentFilePath()
	{
		$this->fw->environmentFilePath();
	}
	*/

	public function environment()
	{
		return $this->fw->environment( func_get_args() );
	}

	public function isLocal()
	{
		return $this->fw->environment( 'local' );
	}

	/*
	public function detectEnvironment( Closure $callback )
	{
		$this->fw->detectEnvironment( $callback );
	}
	*/

	public function runningInConsole()
	{
		$this->fw->runningInConsole();
	}

	public function runningUnitTests()
	{
		$this->fw->runningUnitTests();
	}

	public function registerConfiguredProviders()
	{
		$this->fw->registerConfiguredProviders();
	}

	public function register( $provider, $options = [], $force = false )
	{
		$this->fw->providers->add( $provider );
		// $this->fw->register( $provider, $options, $force ); // HMM?
	}

	public function getProvider( $provider )
	{
		$this->fw->getProvider( $provider );
	}

	public function resolveProviderClass( $provider )
	{
		$this->fw->resolveProviderClass( $provider );
	}

	public function loadDeferredProviders()
	{
		$this->fw->loadDeferredProviders();
	}

	public function loadDeferredProvider( $service )
	{
		$this->fw->loadDeferredProvider( $service );
	}

	public function registerDeferredProvider( $provider, $service = null )
	{
		$this->fw->registerDeferredProvider( $provider, $service );
	}

	public function make( $abstract, array $parameters = [] )
	{
		$this->bindings->make( $abstract, $parameters );
	}

	public function bound( $abstract )
	{
		$this->bindings->bound( $abstract );
	}

	public function isBooted()
	{
		$this->fw->isBooted();
	}

	public function boot()
	{
		$this->fw->boot();
	}

	public function booting( $callback )
	{
		$this->fw->booting( $callback );
	}

	public function booted( $callback )
	{
		$this->fw->booted( $callback );
	}

	public function handle( SymfonyRequest $request, $type = self::MASTER_REQUEST, $catch = true )
	{
		$this->fw->handle( $request, $type, $catch );
	}

	public function shouldSkipMiddleware()
	{
		$this->fw->shouldSkipMiddleware();
	}

	public function configurationIsCached()
	{
		$this->fw->configurationIsCached();
	}

	public function getCachedConfigPath()
	{
		$this->fw->getCachedConfigPath();
	}

	public function routesAreCached()
	{
		$this->fw->routesAreCached();
	}

	public function getCachedRoutesPath()
	{
		$this->fw->getCachedRoutesPath();
	}

	public function getCachedCompilePath()
	{
		$this->fw->getCachedCompilePath();
	}

	public function getCachedServicesPath()
	{
		$this->fw->getCachedServicesPath();
	}

	public function isDownForMaintenance()
	{
		$this->fw->isDownForMaintenance();
	}

	public function abort( $code, $message = '', array $headers = [] )
	{
		$this->fw->abort( $code, $message, $headers );
	}

	public function terminating( Closure $callback )
	{
		$this->fw->terminating( $callback );
	}

	public function terminate()
	{
		$this->fw->terminate();
	}

	public function getLoadedProviders()
	{
		$this->fw->getLoadedProviders();
	}

	public function getDeferredServices()
	{
		$this->fw->getDeferredServices();
	}

	public function setDeferredServices( array $services )
	{
		$this->fw->setDeferredServices( $services );
	}

	public function addDeferredServices( array $services )
	{
		$this->fw->addDeferredServices( $services );
	}

	public function isDeferredService( $service )
	{
		$this->fw->isDeferredService( $service );
	}

	public function configureMonologUsing( callable $callback )
	{
		$this->fw->configureMonologUsing( $callback );
	}

	public function hasMonologConfigurator()
	{
		$this->fw->hasMonologConfigurator();
	}

	public function getMonologConfigurator()
	{
		$this->fw->getMonologConfigurator();
	}

	public function getLocale()
	{
		$this->fw->getLocale();
	}

	public function setLocale( $locale )
	{
		$this->fw->setLocale( $locale );
	}

	public function isLocale( $locale )
	{
		$this->fw->isLocale( $locale );
	}

	public function registerCoreContainerAliases()
	{
		$this->fw->registerCoreContainerAliases();
	}

	public function flush()
	{
		$this->fw->flush();
	}

	public function getNamespace()
	{
		$this->fw->getNamespace();
	}

	public function alias( $abstract, $alias )
	{
		$this->bindings->alias( $abstract, $alias );
	}

	public function tag( $abstracts, $tags )
	{
		$this->bindings->tag( $abstracts, $tags );
	}

	public function tagged( $tag )
	{
		return $this->bindings->tagged( $tag );
	}

	public function bind( $abstract, $concrete = null, $shared = false )
	{
		$this->bindings->bind( $abstract, $concrete, $shared );
	}

	public function bindIf( $abstract, $concrete = null, $shared = false )
	{
		$this->bindings->bindIf( $abstract, $concrete, $shared );
	}

	public function singleton( $abstract, $concrete = null )
	{
		$this->bindings->singleton( $abstract, $concrete );
	}

	public function share( Closure $closure )
	{
		return $this->bindings->share( $closure );
	}

	public function isShared( $abstract )
	{
		return $this->bindings->isShared( $abstract );
	}

	public function extend( $abstract, Closure $closure )
	{
		$this->bindings->extend( $abstract, $closure );
	}

	public function instance( $abstract, $instance )
	{
		$this->bindings->instance( $abstract, $instance );
	}

	public function when( $concrete )
	{
		return $this->bindings->when( $concrete );
	}

	public function call( $callback, array $parameters = [], $defaultMethod = null )
	{
		return $this->bindings->call( $callback, $parameters, $defaultMethod );
	}

	public function resolved( $abstract )
	{
		return $this->bindings->resolved( $abstract );
	}

	public function resolving( $abstract, Closure $callback = null )
	{
		return $this->bindings->resolving( $abstract, $callback );
	}

	public function afterResolving( $abstract, Closure $callback = null )
	{
		return $this->bindings->afterResolving( $abstract, $callback );
	}

	public function rebinding( $abstract, Closure $callback )
	{
		return $this->bindings->rebinding( $abstract, $callback );
	}

	public function offsetExists( $key )
	{
		return $this->bindings->offsetExists( $key );
	}

	public function offsetGet( $key )
	{
		return $this->bindings->offsetGet( $key );
	}

	public function offsetSet( $key, $value )
	{
		$this->bindings->offsetSet( $key, $value );
	}

	public function offsetUnset( $key )
	{
		$this->bindings->offsetUnset( $key );
	}
}
