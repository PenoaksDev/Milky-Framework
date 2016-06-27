<?php
$fw = require( "framework/Loader.php" ); // Replace 'framework' with the location of Penoaks Framework.

/*
 * init() function sets up a basic framework instance and loads Composer classes.
 * The first argument sets the application directory.
 * The second argument (optional) sets the 'config' directory. Not defined (or null), will set it as 'config' under the application directory.
 */
$fw->init( __DIR__ );

/*
 * join() function takes over the current request and returns a response.
 * Be sure that no data was output before this, else problems could arise.
 */
$fw->join();
