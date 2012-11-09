<?
	/*
	 * Name: Chiori Framework API
	 * Version: 5.0.1106 (Apple Bloom)
	 * Last Updated: November 6th, 2012
	 *
	 * (C) 2012 Chiori Greene
	 * All Rights Reserved.
	 * @Author: Chiori Greene
	 * @E-Mail: chiorigreene@gmail.com
	 * @Website: http://web.chiorichan.com
	 * @Open Source License: GNU Public License Version 2
	 *
	 * This code is intellectual property of Chiori Greene and can only be distributed in whole with its
	 * framework which is known as Chiori Framework.
	 *
	 * Description:
	 * This file is the sole framework controller to the Chiori Framework.
	 * On first use this framework needs no other files except this controller and be placed within
	 * a PHP writtable directory -- /protected recommmended. Each time this framework is initalized,
	 * this controller will attempt to automaticly download and update used components.
	 *
	 * On a production server we recommend disabling automatic updating and downloading of components.
	 * But in exchange creating a cronjob to replace this need. See our wiki for instructions.
	 *
	 */

	define ( "DIRSEP", "/" );
	define ( "__BEGIN_TIME__", time() );
	define ( "__FW__", dirname(__FILE__) );
	define ( "FW", dirname(__FILE__) . DIRSEP );
	
	/* Define Yes and No as alternatives to True and False. */
	define ( "yes", true );
	define ( "no", false );
	
	/*
	 * Special Function used to replace the not so great working array_merge.
	 * The built-in array_merge would only merge the first level of the array
	 * but would overwrite any sub arrays. This subroutine fixes that.
	*/
	function arrayMerge() {
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
	
	/*
	 * Smart Require Function
	 */
	function __require($package)
	{
		if ( $package == null || empty($package))
			return false;
		
		$path = FW . str_replace(".", DIRSEP, $package) . ".php";
		
		echo $path;
		
		if (file_exists($path))
		{
			require( $path );
		}
		
		return false;
	}
	
	/*
	 * Some built-in PHP functions are unable to call objected classes
	 * so this function is a backdoor to this limitation.
	 */
	function __classCaller($function, $args = "")
	{
		$chiori = fw();
	
		if ($chiori != null)
		{
			@$chiori->$function($args);
		}
	}
	
	/*
	 * Register ShutdownHandler function to be called on shutdown to handle
	 * some odd jobs. __classCaller is used as a proxy since register_shutdown_function
	 * can not call functions within a class object.
	 */
	//register_shutdown_function("__classCaller", "ShutdownHandler");
	// TODO: Implement this method. Maybe.
	
	__require("com.chiorichan.ChioriFramework");
	
	$chiori = new ChioriFramework();
	
	$chiori->initalizeFramework();
	
	
	
	
	
	