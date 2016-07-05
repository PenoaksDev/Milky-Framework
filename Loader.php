<?php
use Foundation\Support\Facades\Log;

define( 'FRAMEWORK_START', microtime( true ) );

/* Force the display of errors */
ini_set( 'display_errors', 'On' );

/**
 * Super simple exception handler, should never be seen if framework is operating normally
 */
set_exception_handler( function ( Exception $e )
{
	echo "<h1 style='margin-bottom: 0;'>Exception Uncaught</h1><br /><b>" . ( new ReflectionClass( $e ) )->getShortName() . ": " . $e->getMessage() . "</b><br />\n";
	echo( "<pre>" . $e->getTraceAsString() . "</pre>" );
	die();
} );

/*
 * Creates class aliases for missing classes, intended for loose prototyping.
 * TODO: Log developer warnings when these are used
 * TODO Implement a strict mode for production env so that these are not permited.
 */
spl_autoload_register( function ( $class )
{
	$className = end( explode( '\\', $class ) );
	if ( class_exists( $className ) ) // Check if we can alias the class to a root class
	{
		$reflection = new ReflectionClass( $className );
		if ( $reflection->isUserDefined() )
		{
			class_alias( $className, $class );
		}
		else if ( !$reflection->isFinal() )
		{
			// class_alias() is not allowed to alias nonuser-defined PHP classes, so instead we artificially extend them.
			$namespace = explode( '\\', $class );
			array_pop( $namespace );
			$namespace = implode( '\\', $namespace );
			$cls = <<<EOF
namespace $namespace;
class $className extends \\$className {}
EOF;
			eval( $cls );
		}
	}
	else if ( class_exists( "Foundation\\Autoload\\" . $className ) ) // Check if we can alias the class to our Autoload classes
	{
		if ( class_alias( "Foundation\\Autoload\\" . $className, $class ) )
		{
			if ( class_exists( 'Log' ) )
			{
				Log::debug( "Set class alias [" . $class . "] to [Foundation\\Autoload\\" . $className . "]" );
			}
		}
	}
	else if ( class_exists( "Foundation\\Support\\" . $className ) ) // Cleck if we can alias the class to our Support classes -- HMMMMM????
	{
		if ( class_alias( "Foundation\\Support\\" . $className, $class ) )
		{
			if ( class_exists( 'Log' ) )
			{
				Log::debug( "Set class alias [" . $class . "] to [Foundation\\Support\\" . $className . "]" );
			}
		}
	}
} );

/**
 * @param $params
 * @param $paths
 * @return \Foundation\Framework
 */
function initFramework( $params, $paths )
{
	/* If $paths is not an array, make it one and assume the string is the base path */
	if ( !is_array( $paths ) )
	{
		$paths = ['base' => $paths];
	}

	/* Make sure project base directory is set */
	if ( !array_key_exists( 'base', $paths ) )
	{
		if ( error_reporting() == E_STRICT )
		{
			throw new RuntimeException( "You must specify the project base directory." );
		}
		else
		{
			$paths['base'] = realpath( __DIR__ . '/..' );
		}
	}

	/* Strip trailing slashes */
	foreach ( $paths as $key => $val )
	{
		$paths[$key] = rtrim( $val, '\/' );
	}

	/* Add additional missing paths */
	foreach ( ['src', 'config', 'vendor', 'cache', 'vendor'] as $key )
	{
		if ( !array_key_exists( $key, $paths ) )
		{
			$paths[$key] = $paths['base'] . '/' . $key;
		}
	}

	/* Register the Compose Auth Loader */
	$loader = require $paths['vendor'] . '/autoload.php';

	/* Since Composer already implements a decent classloader, we'll just
	 * utilize it by setting where it can find our framework classes */
	$loader->set( 'Foundation', [__DIR__] );

	/* Load all built-in functions */
	require __DIR__ . '/Functions.php';

	/* Initialize a new instance of the Framework Constructor */
	$fw = new \Foundation\Framework( $loader, $params, $paths );

	/* Return newly started framework */

	return $fw;
}
