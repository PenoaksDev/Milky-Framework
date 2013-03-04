<?
	/**
	 * Chiori Framework Version 4
	 * Default Loader File
	 * Author: Chiori Greene
	 * Copyright: (C) 2012 Greenetree LLC
	 *
	 * PHP version 5.3
	 */

	define("__ROOT__", dirname(__FILE__));

	/* Site specific configuration data
	 * See protected/framework.php for configuration values.
	 */
	$conf = array(
		"debug" => array(
			"log" => 9
			),
		"exception_handling" => true,
		"error_handling" => true,
		"title" => "Apple Bloom Company",
		"domain" => "web.applebloom.co",
		"aliases" => array(
			"css" => "http://web.applebloom.co/css",
			"js" => "http://web.applebloom.co/js",
			"img" => "http://web.applebloom.co/img"
			),
		"components" => array(
			"com.chiorichan.modules.users" => array(
				"scripts" => array(
					"login-form" => "http://accounts.applebloom.co/login",
					"login-post" => "http://panel.applebloom.co/"
				),
				"db" => array(
					"username_fields" => array("username", "userID", "phone", "email")
					)
				),
			"com.chiorichan.modules.db" => array(
				"database" => "chiori",
				"username" => "fwuser",
				"password" => "fwpass"
				),
			"com.chiorichan.modules.template" => array(
				"title" => "Apple Bloom Company",
				"metatags" => array(),
				"source" => "/pages"
				),
			"com.chiorichan.modules.settings" => array(
					"db_obj" => "com.chiorichan.modules.db",
					"table_default" => "",
					"table_custom" => ""
				),
			"com.chikislist.modules.email",
			"com.chiorichan.modules.locks",
//			"com.chiorichan.modules.barcodes",
			"com.chiorichan.modules.xmlto",
			"com.chiorichan.modules.xmlfrom",
			"co.applebloom.modules.txt"
		)
	);

	/* Include Framework File - Framework will automaticly initalize from here. */
	require(dirname(__FILE__) . "/protected/framework.php");

	/* Site specific configuration updates - Done here because we need the settings modules inorder to retrieve these settings from the site database. */
	$updateConf = array();

	/* When the subroutines are available we would not recommend updating log settings this way. */
	$file_log_level = $chiori->nameSpaceGet("com.chiorichan.modules.settings")->get("CHIORI_LOG_FILE_LEVEL");
	if ($file_log_level != "LOG_DISABLED")
		$updateConf["debug"]["log"] = $chiori->logLevelInt($file_log_level);

	$file_log_file = $chiori->nameSpaceGet("com.chiorichan.modules.settings")->get("CHIORI_LOG_FILE_PATH");
	if ($file_log_file != "")
		$updateConf["debug"]["log-path"] = $chiori->patchPath($file_log_file);

	$chiori->confMerge($updateConf);

	$date_format = $chiori->nameSpaceGet("com.chiorichan.modules.settings")->get("CHIORI_DATE_FORMAT");

	/* Start virtual page load from database - This can be ignored if you prefer to handle your own page handling. */
	$chiori->nameSpaceGet("com.chiorichan.modules.template")->loadPage($_GET["page_request"]);
?>
