<?
	__require("com.chiorichan.ConsoleSender");
	__require("com.chiorichan.threader.thread");
	__require("com.chiorichan.plugin.Plugin");
	
	Class ChioriFramework
	{
		private $consoleSender;
		private $version = "5.0.1106 (Fluttershy)";
		private $copyright = "Copyright © 2012 Apple Bloom Company (Chiori Greene)";
		private $product = "Chiori Framework";
		
		public function __construct()
		{
			$this->consoleSender = new ConsoleSender();
		}
		
		public function initalizeFramework()
		{
			$this->consoleSender->sendMessage("&4Now Initalizing " . $this->product . " " . $this->version);
			
			
			
		}
		
		public function shutdown()
		{
			// TODO: Make call to several plugins and execute a shutdown event
			exit;
		}
		
		public function getPluginManager()
		{
			
		}
		
		public function getServer()
		{
			
		}
		
		public function setServer()
		{
			
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