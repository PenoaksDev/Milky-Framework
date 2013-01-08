<?
	/**
	 * @Product: Chiori Framework API
	 * @Version 5.1.0106 (Scootaloo)
	 * @Last Updated: January 6th, 2013
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
	 * This file is the sole core class to the Chiori Framework.
	 */

	__Require("com.chiorichan.Colors");	
	__Require("com.chiorichan.ConfigurationManager");
	__Require("com.chiorichan.DaemonSender");
	__Require("com.chiorichan.PluginManager");
	__Require("com.chiorichan.Functions");
	__Require("com.chiorichan.Server");
	__Require("com.chiorichan.DatabaseEngine");
	__Require("com.chiorichan.UserService");
	__Require("com.chiorichan.event.Event");
	__Require("com.chiorichan.plugin.Plugin");
	__Require("com.chiorichan.hooks.Hook");
	
	__Require("com.chiorichan.exception.*");
	
	Class ChioriFramework5
	{
		protected $functions;
		protected $server;
		protected $daemonSender;
		protected $databaseEngine;
		protected $pluginManager;
		protected $userService;
		protected $config;
		protected $version = "5.1.0106 (Scootaloo)";
		protected $copyright = "Copyright © 2013 Apple Bloom Company";
		protected $product = "Chiori Framework";
		protected $initFinished = false;
		protected $hooks = array();
		
		protected $log_levels = array("dump", "sql", "syslog", "file");
		
		protected $siteID = "";
		public $siteTitle = "Unnamed Chiori Framework Site";
		public $domainName = "example.com";
		protected $siteData = "/pages";
		protected $metaTags = array();
		protected $protected = array();
		protected $aliases = array();
				
		/**
		 * Check that only one instance of this class has been created.
		 */
		public function __construct()
		{
			if ( getFramework() != null )
				return getFramework();
		}
		
		public function nameSpaceInclude($package)
		{
			return $this->server->includePackage($package);
		}
		
		public function getVersion() { return $this->version; }
		public function getCopyright() { return $this->copyright; }
		public function getProduct() { return $this->product; }
		
		/**
		 * Call this method to initalize framework after varable was created.
		 * Use this space to load nested classes, fw config and add fw plugins.
		 */
		public function initalizeFramework($config = null)
		{
			if ( $this->initFinished )
				return false;
			
			$this->initFinished = true;
			
			$this->functions = new Functions();
			$this->server = new Server();
			$this->daemonSender = new DaemonSender();
			$this->databaseEngine = new DatabaseEngine();
			$this->config = new ConfigurationManager();
			$this->pluginManager = new PluginManager();
			$this->userService = new UserService();
			
			// Report Debug that Framework is Loading
			$this->server->Debug3("&5Now Initalizing " . $this->product . " " . $this->version);
			$this->server->Debug3("&5" . $this->copyright);
			$this->server->Debug3("&5Framework Spawned as PID: " . getmypid());
			
			// Attempt to load default framework configuration
			$this->server->Debug3("&5Loading Framework Configuration");
			
			try
			{
				$this->getConfig()->loadConfig( FW . DIRSEP . "framework.yml", CONFIG_FW );
			}
			catch ( Exception $e )
			{
				getFramework()->server->sendException("&4" . $e);
				getFramework()->generateExceptionPage( $e );
				$this->shutdown();
			}
			
			if ( $config != null )
			{
				$this->server->Debug3("&5Loading Site Configuration");
					
				try
				{
					$this->getConfig()->loadConfig( $config, CONFIG_SITE );
				}
				catch ( Exception $e )
				{
					getFramework()->server->sendException("&4" . $e);
					$this->shutdown();
				}
				
				$this->siteTitle = $this->getConfig()->getString("title", "Unnamed Chiori Framework Site");
				$this->domainName = $this->getConfig()->getString("domain", "example.com");
				$this->megaTags = $this->getConfig()->getArray("metatags", array());
				$this->siteData = $this->getConfig()->getString("source", "/pages");
				$this->aliases = $this->getConfig()->getArray("aliases", array());
				$this->protected = $this->getConfig()->getArray("protected", array());
			}
			
			// Analize configuration and take action
			// TODO: Needs some alterations
			if ( $this->getConfig()->getBoolean("exception-handling", CONFIG_FW) )
				set_exception_handler(function($e) { getFramework()->getFunctions()->exceptionHandler($e); });
			
			if ( $this->getConfig()->getBoolean("error-handling", CONFIG_FW) )
				set_error_handler(function($no,$str,$file,$line){ $e = new ErrorException($str,$no,0,$file,$line); getFramework()->getFunctions()->exceptionHandler($e); });
			
			$this->log_levels["dump"] = $this->getConfig()->getInt("debug.dump", -1, CONFIG_FW);
			$this->log_levels["sql"] = $this->getConfig()->getInt("debug.sql", -1, CONFIG_FW);
			$this->log_levels["syslog"] = $this->getConfig()->getInt("debug.syslog", -1, CONFIG_FW);
			$this->log_levels["file"] = $this->getConfig()->getInt("debug.file", -1, CONFIG_FW);
			
			$log_path = $this->getConfig()->getString("debug.file-path", "/var/log/chiori.log", CONFIG_FW);
			
			if ( !file_exists( $log_path ) && !is_dir( $log_path ) && $this->log_levels["file"] > -1 )
				throw new LogException("No valid log location defined!");
			
			if ( !is_writable( $log_path ) && $this->log_levels["file"] > -1 )
				throw new LogException("Log location is not writeable by the webserver!");
			
			$siteID = $this->getConfig()->getString("siteID", CONFIG_SITE);
			
			if ( $siteID != null )
			{
				if ( $this->getDatabaseEngine()->getPDO(CONFIG_FW) == null )
				{
					$this->getServer()->Warning("&4Site configuration defines a site id but this framework has no database configured. Database required.");
				}
				else
				{
					$result = $this->getDatabaseEngine()->selectOne("sites", array( "siteID" => $siteID ), CONFIG_FW );
					
					$this->siteID = $siteID;
					
					if ( !empty($result["title"]) )
						$this->siteTitle = $result["title"];
					if ( !empty($result["domain"]) )
						$this->domainName = $result["domain"];
					if ( !empty($result["metatags"]) )
						$this->metaTags = arrayMerge($this->metaTags, json_decode($result["metatags"], true));
					if ( !empty($result["source"]) )
						$this->siteData = $result["source"];
					if ( !empty($result["aliases"]) )
						$this->aliases = arrayMerge($this->aliases, json_decode($result["aliases"], true));
					if ( !empty($result["protected"]) )
						$this->protected = arrayMerge($this->protected, json_decode($result["protected"], true));
					
					$result = $this->getDatabaseEngine()->select("plugins", array( "siteID" => $siteID ), array(), CONFIG_FW);
					
					foreach ( $result as $plugin )
					{
						$this->getPluginManager()->addPluginByName($plugin["namespace"], json_decode($plugin["config"]));
					}
					
					$result = $this->getDatabaseEngine()->select("hooks", array( "siteID" => $siteID ), array(), CONFIG_FW);
						
					foreach ( $result as $hook )
					{
						$this->enableHook( $hook["namespace"] );
					}
				}
			}
			
			$this->server->initSession();
			
			return true;
		}
		
		public function initFrameworkInternal ()
		{
			$this->server->Warning("&4Fallover Framework Configuration Activated");
			
			// Reinit Components
			$this->functions = new Functions();
			$this->server = new Server();
			$this->daemonSender = new DaemonSender();
			$this->databaseEngine = null; // new DatabaseEngine();
			$this->config = new ConfigurationManager();
			$this->pluginManager = null; // new PluginManager();
			$this->userService = null; // new UserService();
			
			$this->getConfig()->loadFalloverConfig();
			
			$this->siteTitle = "Chiori Framework";
			$this->domainName = $_SERVER["SERVER_ADDR"];
			$this->megaTags = array();
			$this->siteData = "/protected5/pages";
			$this->aliases = array();
			$this->protected = array();
		}
		
		public function generateExceptionPage ( $e )
		{
			$this->initFrameworkInternal();
			
			$this->server->Error("&4" . $e->getMessage());
			$GLOBALS["lasterr"] = $e;
			//$GLOBALS["stackTrace"] = $e->stackTrace();
			
			$plugin = $this->createPlugin( "com.chiorichan.plugin.Template" );
			$plugin->loadPage ( "com.chiorichan.themes.error", "", "", "/panic.php" );
		}
		
		/*
		 * Add a hook to the Framework
		 * Currently Supported Method Hooks: shutdown, auth, ...more to come
		 */
		public function enableHook ( $nameSpace )
		{
			if ( strpos($nameSpace, ".") === false )
				$nameSpace = "com.chiorichan.hooks." . $nameSpace;
			
			if ( !__require($nameSpace) )
				return false;
			
			$hookPackage = getFramework()->getFunctions()->getPackageName($nameSpace);
			
			if ( class_exists($hookPackage) )
				$newHook = new $hookPackage();
			
			if ( $newHook == null || $newHook == false )
				return false;
			
			if ( method_exists($newHook, "auth") )
			{
				$this->hooks[] = array("name" => "auth", "class" => $newHook, "method" => "auth");
				/*
				if ( $this->userService->addHook( $newHook ) )
				{
					$this->server->Debug3("&5Successfully Enabled Auth Hook: " . $nameSpace);
				}
				else
				{
					$this->server->Warning("&4Failure Enabling Auth Hook: " . $nameSpace);
				}
				*/
			}
			
			if ( method_exists($newHook, "shutdown") )
				$this->hooks[] = array("name" => "shutdown", "class" => $newHook, "method" => "shutdown");
			
			if ( method_exists($newHook, "customHooks") )
			{
				$customHooks = $newHook->customHooks();
				
				if ( is_array( $customHooks ) )
				{
					foreach ( $customHooks as $hook )
					{
						if ( method_exists($newHook, $hook ) )
						{
							$this->hooks[] = array("name" => $hook, "class" => $newHook, "method" => $hook);
						}
					}
				}
			}
			
			return true;
		}
		
		/*
		 * Allows your site or plugin to manually call hooks.
		 * Primary use is for custom hooks.
		 * Your hook class must return an array of custom hook names when a call
		 * is made to the $hookClass->customHooks() method for custom hooks to work. 
		 */
		public function doHook ( $hookName, $parms = array() )
		{
			foreach ( $this->hooks as $hook )
			{
				if ( $hook[0] == $hookName || $hook["name"] == $hookName )
				{
					$class = ( isset($hook["class"]) ) ? $hook["class"] : $hook[1];
					$method = ( isset($hook["method"]) ) ? $hook["method"] : $hook[2];
					
					if ( $class != null && $method != null )
					{
						$result = $class->$method( $parms );
						
						if ( $result != null && $result != false )
							return $result;
					}
				}
			}
			
			return null;
		}
		
		public function getSiteTitle ()
		{
			return $this->siteTitle;
		}
		
		public function getDomainName ()
		{
			return $this->domainName;
		}
		
		public function getMetaTags ()
		{
			return $this->metaTags;
		}
		
		public function getSource ()
		{
			return $this->siteData;
		}
		
		public function getAliases ()
		{
			return $this->aliases;
		}
		
		public function getProtected ()
		{
			return $this->protected;
		}
		
		public function shutdown()
		{
			if ( !$this->initFinished )
				return false;

			// TODO: Make call to several plugins and execute a shutdown event
			//$this->getPluginManager()->raiseEventbyName("FrameworkShutdown");
			$this->doHook("shutdown");
			
			$this->initFinished = false;
			die();
		}
		
		public function getDaemonSender()
		{
			if ( !$this->initFinished )
				return null;
			
			return $this->daemonSender;
		}
		
		public function getPluginManager()
		{
			if ( !$this->initFinished )
				return null;
			
			return $this->pluginManager;
		}
		
		public function getUserService()
		{
			if ( !$this->initFinished )
				return null;
			
			return $this->userService;
		}
		
		public function getConfig()
		{
			if ( !$this->initFinished )
				return null;
			
			return $this->config;
		}
		
		public function getConfigurationManager()
		{
			return $this->getConfig();
		}
		
		public function getServer()
		{
			if ( !$this->initFinished )
				return null;
			
			return $this->server;
		}
		
		public function getFunctions()
		{
			if ( !$this->initFinished )
				return null;

			return $this->functions;
		}
		
		public function getDatabaseEngine()
		{
			if ( !$this->initFinished )
				return null;
			
			return $this->databaseEngine;
		}
		
		public function createPlugin ($pluginName, $config = null)
		{
			if ( strpos($pluginName, ".") === false )
				$pluginName = "com.chiorichan.plugin." . $pluginName;
			
			if ( !__require($pluginName) )
				return false;
				
			$plugin = getFramework()->getFunctions()->getPackageName($pluginName);
			
			if ( class_exists($plugin) )
				return new $plugin($config);
		
			return null;
		}
		
		public function createEvent ($eventName)
		{
			if ( !$this->initFinished )
				return null;
			
			if ( strpos($eventName, ".") === false )
				$eventName = "com.chiorichan.event." . $eventName;
				
			if ( !__Require($eventName) )
				return false;
				
			$event = getFramework()->getFunctions()->getPackageName($eventName);
				
			if ( class_exists($event) )
				return new $event();
				
			return null;
		}
	}
