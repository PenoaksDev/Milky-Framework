<?
	__Require("com.chiorichan.Colors");	
	__Require("com.chiorichan.ConfigurationManager");
	__Require("com.chiorichan.DaemonSender");
	__Require("com.chiorichan.PluginManager");
	__Require("com.chiorichan.Functions");
	__Require("com.chiorichan.Server");
	__Require("com.chiorichan.DatabaseEngine");
	__Require("com.chiorichan.UserService");
	__Require("com.chiorichan.plugin.Plugin");
	__Require("com.chiorichan.event.Event");
	__Require("com.chiorichan.exception.Config");
	
	Class ChioriFramework
	{
		protected $functions;
		protected $server;
		protected $daemonSender;
		protected $databaseEngine;
		protected $pluginManager;
		protected $userService;
		protected $config;
		protected $version = "5.0.1111 (Fluttershy)";
		protected $copyright = "Copyright © 2012 Apple Bloom Company (Chiori Greene)";
		protected $product = "Chiori Framework";
		private $initFinished = false;
		
		public function __construct($config)
		{
			/*
			 * Check that only one instance of this class has been created.
			 */
			if ( getFramework() != null )
				return getFramework();
			
			// Load Basic Classes
			$this->functions = new Functions();
			$this->server = new Server();
			$this->daemonSender = new DaemonSender();
			
			// Report Debug that Framework is Loading
			$this->daemonSender->sendDebug("&5Now Initalizing " . $this->product . " " . $this->version, LOG_DEBUG);
			$this->daemonSender->sendDebug("&5" . $this->copyright, LOG_DEBUG);
			
			$this->databaseEngine = new DatabaseEngine();

			$this->daemonSender->sendDebug("&5Loading Configuration");
			$this->config = new ConfigurationManager();
			
			try
			{
				$this->getConfig()->loadConfig( $config );				
			}
			catch ( ConfigException $exception )
			{
				throw $exception;
			}
			
			$this->pluginManager = new PluginManager();
			$this->userService = new UserService();
		}
		
		/**
		 * Call this once framework varable is initalized.
		 * Use this space to load fw config and add fw plugins.
		 */
		public function initalizeFramework()
		{
			if ( $this->initFinished )
				return false;
			
			
			
			$this->initFinished = true;
			return true;
		}
		
		public function shutdown()
		{
			if ( !$initFinished )
				return false;
			
			// TODO: Make call to several plugins and execute a shutdown event
			$this->getPluginManager()->raiseEventbyName("FrameworkShutdown");
			
			$initFinished = false;
		}
		
		public function getDaemonSender()
		{
			return $this->daemonSender;
		}
		
		public function getPluginManager()
		{
			return $this->pluginManager;
		}
		
		public function getUserService()
		{
			return $this->userService;
		}
		
		public function getConfig()
		{
			return $this->config;
		}
		
		public function getServer()
		{
			return $this->server;
		}
		
		public function getFunctions()
		{
			return $this->functions;
		}
		
		public function getDatabaseEngine()
		{
			return $this->databaseEngine;
		}
		
		public function buildPlugin (string $pluginName)
		{
			if ( strpos($pluginName, ".") === false )
				$pluginName = "com.chiorichan.plugin." . $pluginName;
			
			if ( !__require($pluginName) )
				return false;
				
			$plugin = getFramework()->getFunctions()->getPackageName($pluginName);
	
			if ( class_exists($plugin) )
				return new $plugin();
		
			return null;
		}
		
		public function buildEvent (string $eventName)
		{
			if ( strpos($eventName, ".") === false )
				$eventName = "com.chiorichan.event." . $eventName;
				
			if ( !__Require($eventName) )
				return false;
				
			$event = getFramework()->getFunctions()->getPackageName($eventName);
				
			if ( class_exists($event) )
				return new $event();
				
			return null;
		}
		
		public function banIP()
		{
			
		}
		
		public function unbanIP()
		{
			
		}
		
		public function setWhitelist($bool)
		{
			if ( typeof($bool) != "Boolean" )
				return false;
			
		}
		
		public function getServerName()
		{
			return  $this->getServer()->serverName();
		}
	}