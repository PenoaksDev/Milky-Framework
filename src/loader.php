<?php

/**
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */

use Milky\Helpers\Arr;
use Milky\Helpers\Breadcrumbs;
use Milky\Helpers\Str;
use Symfony\Component\Finder\Finder;

define( 'FRAMEWORK_START', microtime( true ) );

define( '__', DIRECTORY_SEPARATOR );
define( '__FW__', __DIR__ );
define( "yes", true );
define( "no", false );

/* Force the display of errors */
ini_set( 'display_errors', 'On' );

/* Disable */
umask( 0 );

/* Prevent the display of E_NOTICE and E_STRICT */
error_reporting( E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED );

/**
 * Super simple exception handler, should never be seen if framework is operating normally
 */
set_exception_handler( function ( $e )
{
	if ( !$e instanceof Exception )
		$e = new \Symfony\Component\Debug\Exception\FatalThrowableError( $e );

	echo( "<h1 style='margin-bottom: 0;'>Uncaught Exception</h1><br />\n" );
	echo( "<b>" . ( new ReflectionClass( $e ) )->getShortName() . ": " . $e->getMessage() . "</b><br />\n" );
	echo( "<p>at " . $e->getFile() . " on line " . $e->getLine() . "</p>\n" );
	echo( "<pre>" . $e->getTraceAsString() . "</pre>" );
	die();
} );

/*
 * Creates class aliases for missing classes, intended for loose prototyping.
 * TODO Logging developer warnings when these are used
 * TODO Implement a strict mode for production environments.
 */
spl_autoload_register( function ( $class )
{
	$namespace = explode( '\\', $class );
	$className = array_pop( $namespace );
	if ( class_exists( $className ) ) // Check if we can alias the class to a root class
	{
		developerWarning( $className );

		$reflection = new ReflectionClass( $className );
		if ( $reflection->isUserDefined() )
		{
			class_alias( $className, $class );
		}
		else if ( !$reflection->isFinal() )
		{
			// class_alias() is not allowed to alias non-user defined PHP classes, so instead we artificially extend them.
			$ns = implode( '\\', $namespace );
			eval( "namespace $ns; class $className extends $className {}" );
		}
		else
		{
			die( "Class [" . $class . "] was found but we were unable to alias or extend because it's non-user defined and final." );
		}
	}
	else if ( class_exists( "Penoaks\\" . $className ) )
		if ( class_alias( "Penoaks\\" . $className, $class ) )
			if ( class_exists( 'Logging' ) )
				Log::debug( "Set class alias [" . $class . "] to [Penoaks\\Support\\" . $className . "]" );
} );

/**
 * Alias all the facades, for easy access.
 */
foreach ( Finder::create()->files()->in( __DIR__ . '/Milky/Facades' )->name( '*.php' ) as $file )
{
	$class = str_replace( '.php', '', $file->getFilename() );
	if ( !class_exists( $class ) )
		class_alias( "\\Milky\\Facades\\" . $class, $class );
}

class_alias( Str::class, 'Str' );
class_alias( Arr::class, 'Arr' );
class_alias( Breadcrumbs::class, 'Breadcrumbs' );

/**
 * @return \Milky\Framework
 */
function fw( $basePath = null )
{
	if ( Milky\Framework::isRunning() )
		return Milky\Framework::fw();

	$fw = new Milky\Framework( $basePath );

	// Register class and manager implementations here

	return $fw;
}
