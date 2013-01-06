<?php
	define("__ROOT__", dirname(__FILE__));

	require( "protected5/Constructor.php" );
	
	$chiori = new ChioriFramework5();
	
	// Initalize Framework Class plus Load Configuration
	$chiori->initalizeFramework(dirname(__FILE__) . "/config.yml");
	
	$template = $chiori->getPluginManager()->getPluginbyName("Template");
	
	/* Normal Mode */
	if ( !$template->rewriteVirtual( $_SERVER["REQUEST_URI"] ) )
		$template->localFile( $_SERVER["REQUEST_URI"] );
	
	/* Compat Mode */
	/*
	$template = $template->rewriteVirtual( $_SERVER["REQUEST_URI"], "", true );
	
	if ( $template == null )
		$template->localFile( $_SERVER["REQUEST_URI"] );
	
	if ( $template["compat_mode"] == "1" )
	{
		require( dirname(__FILE__) . "/Loader.php" );
	}
	else
	{
		$this->loadPage($template["theme"], $template["view"], $template["title"], $template["file"], $template["html"], $template["reqlevel"]);
	}
	*/