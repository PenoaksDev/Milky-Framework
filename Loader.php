<?php
define("__ROOT__", __DIR__);

require( "protected/Constructor.php" );

$framework = new \Framework\Loader();

// Initalize Framework Class plus Load Configuration
$framework->initalizeFramework(dirname(__FILE__) . "/config.yml");

$template = $chiori->getPluginManager()->getPluginbyName("Template");

if ( !$template->rewriteVirtual( $_SERVER["REQUEST_URI"] ) )
	$template->localFile( $_SERVER["REQUEST_URI"] );

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
