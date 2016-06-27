<?php

define( 'FRAMEWORK_START', microtime( true ) );
define ( "__FW__", __DIR__ );
define ( "DIRSEP", "/" );
define ( "yes", true );
define ( "no", false );

error_reporting( E_ALL );

/* --------------------------------------------------------------------------
 * Register The Composer Auto Loader
 * --------------------------------------------------------------------------
 *
 * Composer provides a convenient, automatically generated class loader
 * for our application. We just need to utilize it!
 */
$loader = require __DIR__ . '/vendor/autoload.php';

// Tell Composer where to find our framework classes
$loader->set( 'Foundation', [__DIR__] );

// Load framework built-in functions
require __DIR__ . '/Functions.php';

function initFramework( $basePath, $configPath = null )
{
	$fw = new Foundation\Constructor( $basePath, $configPath );

	/* --------------------------------------------------------------------------
	 * Bind Important Interfaces
	 * --------------------------------------------------------------------------
	 *
	 * Next, we need to bind some important interfaces into the container so
	 * we will be able to resolve them when needed. The kernels serve the
	 * incoming requests to this application from both the web and CLI.
	 */

	$fw->singleton(
		Foundation\Contracts\Http\Kernel::class,
		App\Http\Kernel::class
	);

	$fw->singleton(
		Foundation\Contracts\Console\Kernel::class,
		App\Console\Kernel::class
	);

	$fw->singleton(
		Foundation\Contracts\Debug\ExceptionHandler::class,
		App\Exceptions\Handler::class
	);

	return $fw;
}
