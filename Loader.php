<?php
define( 'FRAMEWORK_START', microtime( true ) );
define( '__DS__', DIRECTORY_SEPARATOR );
define( '__FW__', __DIR__ );
define ( "yes", true );
define ( "no", false );

error_reporting( E_ALL );

function initFramework( $paths )
{
	/* If $paths is not an array, make it one */
	if ( !is_array( $paths ) )
		$paths = ['base' => $paths];

	/* Make sure project base directory is set */
	if ( !array_key_exists( 'base', $paths ) )
		throw new RuntimeException( "You must specify the project base directory." );

	/* Strip trailing slashes */
	foreach ( $paths as $key => $val )
		$paths[$key] = rtrim( $val, '\/' );

	/* Set missing keys */
	foreach ( ['src', 'config', 'vendor'] as $key )
		if ( !array_key_exists( $key, $paths ) )
			$paths[$key] = $paths['base'] . '/' . $key;

	/* Register the Compose Auth Loader */
	$loader = require $paths['vendor'] . '/autoload.php';

	/* Since Composer already implements a decent autoloader, we'll just
	 * utilize it by setting where it can find our classes */
	$loader->set( 'Foundation', [__DIR__] );

	/* Load all built-in functions */
	require __DIR__ . '/Functions.php';

	/* Initalize a new instance of the Framework Constructor */
	$fw = new Foundation\Constructor( $paths );

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

	/* Return newly initialized framework */
	return $fw;
}
