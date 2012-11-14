<?
	require( "protected/Constructor.php" );
	
	phpinfo();
	die();
	
	$arr = array(
			"database" => array(
					"type" => "mySQL",
					"database" => "example",
					"username" => "example",
					"password" => "example",
					"prefix" => "fw_"
					)
			);
	
	yaml_emit_file( "./config.yml", $arr );
	
	$chiori = new ChioriFramework();
	
	$chiori->initalizeFramework(dirname(__FILE__) . "/config.yml");
	
	//$chiori->getPluginManager()->addPluginByName("com.chiorichan.plugin.db", $config);
	
	$chiori->shutdown();