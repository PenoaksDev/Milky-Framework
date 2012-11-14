<?php
	class ConfigurationManager
	{
		protected $config;
		
		function __construct()
		{
			$this->config = Array();
		}
		
		public function loadConfig( $config )
		{
			if ( !is_string( $config ) )
			{
				getFramework()->daemonSender->sendException("&4Config Exception: Expecting a configuration location of framework yml config.");
				throw new ConfigException("Expecting a configuration location of framework yml config.");
			}
				
			if ( !file_exists( $config ) )
			{
				getFramework()->daemonSender->sendException("&4Config Exception: Could not find configuration yml file.");
				throw new ConfigException("Could not find configuration yml file.");
			}
			
			$this->config = yaml_parse_file($config);
		}
		
		public function getString( string $keyName )
		{
			$path = explode(".", $keyName);
			$config = $this->config;
			
			foreach ($path as $key)
			{
				if ( array_key_exists($key, $config) )
				{
					$config = $config[$key];
				}
				else
				{
					return null;
				}	
			}
			
			return $config;
		}
	}