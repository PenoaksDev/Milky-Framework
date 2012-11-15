<?
	require( "protected/Constructor.php" );
	
	$chiori = new ChioriFramework();
	
	// Initalize Framework Class plus Load Configuration
	$chiori->initalizeFramework(dirname(__FILE__) . "/config.yml");
	
	// Add Template Plugin
	$chiori->getPluginManager()->addPluginByName("com.chiorichan.plugin.Template");