<?php
/*
 * We must first require the framework loader.
 *
 * If you cloned our repository or forced Composer to install it some place else, be sure to set that location here.
 */
require( "framework/Loader.php" );

/*
 * Directories containing files needed to run Penoaks Framework
 *
 * base (REQUIRED) => The project base directory.
 * src => The directory containing assets, views, controllers, etc.
 * config => The directory containing site configuration.
 * vendor => The directory containing the Composer vendor packages.
 *
 * The framework will assume that undefined path keys are located under the base directory, e.g., 'src' will point to: base directory + '/src'
 */
$paths = [
	'base' => __DIR__,
	'src' => __DIR__ . '/src',
	'config' => __DIR__ . '/config',
	'vendor' => __DIR__ . '/vendor'
];

/*
 * initFramework() function initializes a basic instance of Penoaks Framework.
 * First argument must be either the project base directory or an array of paths, see above.
 */
$fw = initFramework( $paths );

/*
 * join() function takes over the current request and returns a response.
 * Be sure that no data was sent to the client before this, or else problems could arise.
 */
$fw->join();
