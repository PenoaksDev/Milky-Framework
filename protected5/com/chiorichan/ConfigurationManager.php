<?php
	__Require("com.chiorichan.exception.ConfigException");

	class ConfigurationManager
	{
		protected $config = array();
		protected $db = null;
		
		function __construct()
		{
			$this->config = Array();
			$this->db = new DatabaseEngine();
		}
		
		public function loadConfig( $fileLocation, $configLevel = CONFIG_SITE )
		{
			if ( !is_string( $fileLocation ) )
			{
				throw new ConfigException("Expecting a file location of yml config.");
			}
				
			if ( !file_exists( $fileLocation ) )
			{
				throw new ConfigException("Could not find the configuration yml file: \"" . $fileLocation . "\"");
			}
			
			try
			{
				$this->config[$configLevel] = yaml_parse_file($fileLocation);
			}
			catch ( Exception $exception )
			{
				throw $exception;
			}
			
			if ( $this->keyExists("database", $configLevel) )
			{
				// Load Database
				$type = $this->getString("database.type", $configLevel);
				$host = $this->getString("database.host", $configLevel);
				$port = $this->getString("database.port", $configLevel);
				$database = $this->getString("database.database", $configLevel);
				$username = $this->getString("database.username", $configLevel);
				$password = $this->getString("database.password", $configLevel);
				$prefix = $this->getString("database.prefix", $configLevel);
				
				try
				{
					getFramework()->getDatabaseEngine()->buildPDO($database, $configLevel, $type, $username, $password, $host, $prefix);
				}
				catch ( Exception $e )
				{
					throw $e;
				}
			}
		}
		
		public function getConfig( $keyName, $arg2 = null, $arg3 = CONFIG_SITE )
		{
			$default = null;
			$configLevel = CONFIG_SITE;
			
			switch ( $arg2 )
			{
				case null: $configLevel = CONFIG_SITE; $default = null; break;
				case CONFIG_FW: $configLevel = $arg2; break;
				case CONFIG_SITE: $configLevel = $arg2; break;
				case CONFIG_LOCAL0: $configLevel = $arg2; break;
				case CONFIG_LOCAL1: $configLevel = $arg2; break;
				case CONFIG_LOCAL2: $configLevel = $arg2; break;
				case CONFIG_LOCAL3: $configLevel = $arg2; break;
				case CONFIG_LOCAL4: $configLevel = $arg2; break;
				case CONFIG_LOCAL5: $configLevel = $arg2; break;
				case CONFIG_LOCAL6: $configLevel = $arg2; break;
				case CONFIG_LOCAL7: $configLevel = $arg2; break;
				case CONFIG_LOCAL8: $configLevel = $arg2; break;
				case CONFIG_LOCAL9: $configLevel = $arg2; break;
				default: $default = $arg2; $configLevel = $arg3; break; break;
			}
			
			$path = explode(".", $keyName);
			$config = $this->config[$configLevel];
			
			if ( $config == null )
				return $default;
			
			foreach ($path as $key)
			{
				if ( array_key_exists($key, $config) )
				{
					$config = $config[$key];
				}
				else
				{
					return $default;
				}
			}
			
			if ( $config == null )
				return $default;
			
			return $config;
		}
		
		public function keyExists( $keyName, $arg2 = CONFIG_SITE )
		{
			$path = explode(".", $keyName);
			$config = $this->config[$arg2];
			
			if ( $config == null )
				return false;
			
			foreach ($path as $key)
			{
				if ( array_key_exists($key, $config) )
				{
					$config = $config[$key];
				}
				else
				{
					return false;
				}
			}
				
			return true;
		}
		
		public function getString( $keyName, $arg2 = null, $arg3 = CONFIG_SITE )
		{
			return (string) $this->getConfig( $keyName, $arg2, $arg3 );
		}
		
		public function getArray( $keyName, $arg2 = null, $arg3 = CONFIG_SITE )
		{
			return (array) $this->getConfig( $keyName, $arg2, $arg3 );
		}
		
		public function getBoolean( $keyName, $arg2 = null, $arg3 = CONFIG_SITE )
		{
			return (boolean) $this->getConfig( $keyName, $arg2, $arg3 );
		}
		
		public function getInt( $keyName, $arg2 = null, $arg3 = CONFIG_SITE )
		{
			return (int) $this->getConfig( $keyName, $arg2, $arg3 );
		}
		
		public function getDatabase()
		{
			return $this->db;
		}
	}