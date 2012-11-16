<?php
	define("__ROOT__", dirname(__FILE__));

	require( "protected/Constructor.php" );
	
	$chiori = new ChioriFramework();
	
	// Initalize Framework Class plus Load Configuration
	$chiori->initalizeFramework(dirname(__FILE__) . "/config.yml");
	
	$template = $chiori->getPluginManager()->getPluginbyName("Template");
	
	if ( !$template->rewriteVirtual( $_SERVER["REQUEST_URI"] ) )
		$template->localFile( $_SERVER["REQUEST_URI"] );