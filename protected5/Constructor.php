<?
	/**
	 * @Product: Chiori Framework API
	 * @Version 5.1.0326 (Scootaloo)
	 * @Last Updated: March 26th, 2013
	 * @PHP Version: 5.4 or Newer
	 *
	 * @Author: Chiori Greene
	 * @E-Mail: chiorigreene@gmail.com
	 * @Website: http://web.chiorichan.com
	 * @License: GNU Public License Version 2
	 * @Copyright (C) 2013 Chiori Greene. All Rights Reserved.
	 *
	 * This code is intellectual property of Chiori Greene and can only be distributed in whole with its
	 * framework which is known as Chiori Framework.
	 *
	 * Description:
	 * This file is the sole constructor to the Chiori Framework.
	 * On first use this framework needs no other files except this controller and be placed within
	 * a PHP writtable directory -- /protected recommmended. Each time this framework is initalized,
	 * this controller will attempt to automaticly download and update used components.
	 */

	date_default_timezone_set("America/Chicago");
	
	define ( "DIRSEP", "/" );
	define ( "__BEGIN_TIME__", time() );
	//define ( "__FW__", dirname(__FILE__) );
	define ( "FW", dirname(__FILE__) );
	
	defined("__ROOT__") or
		define("__ROOT__", dirname(__FILE__) . "/..");
	
	/* Define Yes and No as alternatives to True and False. */
	define ( "yes", true );
	define ( "no", false );
	
	// Define Configuration Sections
	define ( "CONFIG_FW", "CONFIG_FW" );
	define ( "CONFIG_SITE", "CONFIG_SITE" );
	define ( "CONFIG_LOCAL0", "CONFIG_LOCAL0" );
	define ( "CONFIG_LOCAL1", "CONFIG_LOCAL1" );
	define ( "CONFIG_LOCAL2", "CONFIG_LOCAL2" );
	define ( "CONFIG_LOCAL3", "CONFIG_LOCAL3" );
	define ( "CONFIG_LOCAL4", "CONFIG_LOCAL4" );
	define ( "CONFIG_LOCAL5", "CONFIG_LOCAL5" );
	define ( "CONFIG_LOCAL6", "CONFIG_LOCAL6" );
	define ( "CONFIG_LOCAL7", "CONFIG_LOCAL7" );
	define ( "CONFIG_LOCAL8", "CONFIG_LOCAL8" );
	define ( "CONFIG_LOCAL9", "CONFIG_LOCAL9" );
	
	// Define extra log levels
	define ( "LOG_DISABLED", -1 );
	define ( "LOG_DEBUG1", 8 );
	define ( "LOG_DEBUG2", 9 );
	define ( "LOG_DEBUG3", 10 );
	
	// TODO: Be ready to implement scalar varable types.
	
	define('TYPEHINT_PCRE' ,'/^Argument (\d)+ passed to (?:(\w+)::)?(\w+)\(\) must be an instance of (\w+), (\w+) given/');
	
	class Typehint
	{
	
		private static $Typehints = array(
				'boolean'   => 'is_bool',
				'integer'   => 'is_int',
				'float'     => 'is_float',
				'string'    => 'is_string',
				'resource'  => 'is_resource'
		);
	
		private function __Constrct() {}
	
		public static function initializeHandler()
		{
			set_error_handler('Typehint::handleTypehint');
	
			return true;
		}
	
		private static function getTypehintedArgument($ThBackTrace, $ThFunction, $ThArgIndex, &$ThArgValue)
		{
	
			foreach ($ThBackTrace as $ThTrace)
			{
	
				// Match the function; Note we could do more defensive error checking.
				if (isset($ThTrace['function']) && $ThTrace['function'] == $ThFunction)
				{
	
					$ThArgValue = $ThTrace['args'][$ThArgIndex - 1];
	
					return TRUE;
				}
			}
	
			return FALSE;
		}
	
		public static function handleTypehint($ErrLevel, $ErrMessage)
		{
	
			if ($ErrLevel == E_RECOVERABLE_ERROR)
			{
	
				if (preg_match(TYPEHINT_PCRE, $ErrMessage, $ErrMatches))
				{
	
					list($ErrMatch, $ThArgIndex, $ThClass, $ThFunction, $ThHint, $ThType) = $ErrMatches;
	
					if (isset(self::$Typehints[$ThHint]))
					{
	
						$ThBacktrace = debug_backtrace();
						$ThArgValue  = NULL;
	
						if (self::getTypehintedArgument($ThBacktrace, $ThFunction, $ThArgIndex, $ThArgValue))
						{
	
							if (call_user_func(self::$Typehints[$ThHint], $ThArgValue))
							{
	
								return true;
							}
						}
					}
					throw new Exception($ErrMessage);
				}
			}
	
			return false;
		}
	}
	
	//Typehint::initializeHandler();
	
	/**
	 * Special Function used to replace the not so great working array_merge.
	 * The built-in array_merge would only merge the first level of the array
	 * but would overwrite any sub arrays. This subroutine fixes that.
	 */
	function arrayJoin() {
		if (func_num_args() < 2) {
			trigger_error(__FUNCTION__ .' needs two or more array arguments', E_USER_WARNING);
			return;
		}
		$arrays = func_get_args();
		$merged = array();
		 
		while ($arrays) {
			$array = array_shift($arrays);
			if (!is_array($array)) {
				trigger_error(__FUNCTION__ .' encountered a non array argument', E_USER_WARNING);
				return;
			}
			if (!$array)
				continue;
			foreach ($array as $key => $value)
				if (is_string($key))
				if (is_array($value) && array_key_exists($key, $merged) && is_array($merged[$key]))
				$merged[$key] = call_user_func(__FUNCTION__, $merged[$key], $value);
			else
				$merged[$key] = $value;
			else
				$merged[] = $value;
		}
		return $merged;
	}
	
	/**
	 * Package Integrity Checker
	 */
	function __Package($package)
	{
		$package = FW . DIRSEP . str_replace(".", DIRSEP, $package);
		
		if ( !file_exists($package) )
		{
			// TODO: Add better missing package handler.
			die("FAILURE: Package Folder Missing.");
			exit;
		}
		
		if ( file_exists($package) && !is_dir($package) )
		{
			// TODO: Add better missing package handler.
			die("FAILURE: Package Location is not a Folder.");
			exit;
		}
		
		$global["_package"] = $package;
	}
	
	/**
	 * Smart Require Function
	 */
	function __Require($package)
	{
		if ( $package == null || empty($package))
			return false;
		
		if ( !strpos($package, ".") )
			$package = "com.chiorichan." . $package;
		
		if ( strpos($package, "*") !== false )
		{
			$path = FW . str_replace(".", DIRSEP, $package);
			
			$glob = glob($path);
			
			foreach ( $glob as $file )
			{
				if (file_exists($path))
				{
					if ( getFramework() != null )
						getFramework()->getServer()->sendDebug("&1Loading \"" . $path . "\"");
					require_once( $path );
				}
			}
			
			return true;
		}
		else
		{
			$path = FW . DIRSEP . str_replace(".", DIRSEP, $package) . ".php";
			
			if (file_exists($path))
			{
				if ( getFramework() != null )
					getFramework()->getServer()->Debug3("&1Loading \"" . $path . "\"");
				require_once( $path );
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Some built-in PHP functions are unable to call objected classes
	 * so this function is a backdoor to this limitation.
	 */
	function __fwCaller($function, $args = "")
	{
		$chiori = getFramework();
	
		if ($chiori != null)
		{
			@$chiori->$function($args);
			return true;
		}
		
		return false;
	}
	
	/**
	 * Return first object that has ChioriFramework as Parent Class.
	 * No longer do we search $chiori in the global scope =).
	 */
	function getFramework ()
	{
		foreach ( $GLOBALS as $var )
		{
			if ( is_object($var) && ( get_parent_class($var) == "ChioriFramework5" || get_class($var) == "ChioriFramework5" ) )
				return $var;
			
			if ( is_object($var) && ( get_parent_class($var) == "ChioriFWBase" || get_class($var) == "ChioriFWBase" ) )
				return $var;
		}
		
		return null;
	}
	
	/**
	 * Register ShutdownHandler function to be called on shutdown to handle
	 * some odd jobs. __classCaller is used as a proxy since register_shutdown_function
	 * can not call functions within a class object.
	 */
	register_shutdown_function("__fwCaller", "shutdown");
	
	__require("com.chiorichan.ChioriFramework");
	
	