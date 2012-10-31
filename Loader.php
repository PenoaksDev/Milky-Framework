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
		"title" => "Site Title",
		"domain" => "domain.name",
		"aliases" => array(
			"css" => "http://domain.name/css",
			"js" => "http://domain.name/js",
			"img" => "http://domain.name/img"
			),
		"components" => array(
			"com.chiorichan.modules.users" => array(
				"scripts" => array(
					"login-form" => "http://domain.name/login-form",
					"login-post" => "http://domain.name/user-home"
				),
				"db" => array(
					"username_fields" => array("username", "userID", "phone", "email")
					)
				),
			"com.chiorichan.modules.db" => array(
				"database" => "",
				"username" => "",
				"password" => ""
				),
			"com.chiorichan.modules.template" => array(
				"title" => "Site Title",
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
			"com.chiorichan.modules.barcodes",
			"com.chiorichan.modules.xmlto",
			"com.chiorichan.modules.xmlfrom"
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
