<?php
	__Require("com.chiorichan.exception.ConfigException");

	class ConfigurationManager
	{
		protected $config = array();
		protected $db = null;
		
		function __construct()
		{
			$this->db = new DatabaseEngine();
			$this->config = Array(
				CONFIG_SITE => array(),
				CONFIG_FW => array(),
				CONFIG_LOCAL0 => array(),
				CONFIG_LOCAL1 => array(),
				CONFIG_LOCAL2 => array(),
				CONFIG_LOCAL3 => array(),
				CONFIG_LOCAL4 => array(),
				CONFIG_LOCAL5 => array(),
				CONFIG_LOCAL6 => array(),
				CONFIG_LOCAL7 => array(),
				CONFIG_LOCAL8 => array(),
				CONFIG_LOCAL9 => array());
		}
		
		public function loadFalloverConfig ()
		{
			$this->config[CONFIG_FW] = array();
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
		
		function get($key, $idenifier = -1, $idenifier2 = "", $value_only = true, $default_value = "") // Returns setting string based on idenifier.
		{
			$users = getFramework()->getUserService();
			$db = getFramework()->getDatabaseEngine();
			
			// Check if only one idenifier was provided. i.e. Called from a script not made for Version 2 of this subroutine.
			if (is_bool($idenifier2))
			{
				if (!is_bool($value_only))
					$default_value = $value_only;
		
				$value_only = $idenifier2;
				$idenifier2 = "";
		
				getFramework()->getServer()->Warning("Subroutine: \"settings->get\" called with old arguments pattern.");
			}
				
			$rtn = array("success" => false, "key" => strtoupper($key), "value" => $default_value);
				
			if ($idenifier == -1)
			{
				$idenifier = $users->getString("userID");
				if ($idenifier === false) $idenifier = "";
			}
				
			$result_default = $db->selectOne("settings_default", "`key` = '" . $rtn["key"] . "'");
			if ($result_default === false)
			{
				getFramework()->getServer()->Error("ChioriSettings: Retriving \"" . $key . "\" from database was unsuccessfull, Error: \"Non-existent Setting\", Returning default value of: \"" . $rtn["value"] . "\"");
				return ($value_only) ? $rtn["value"] : $rtn;
			}
				
			$rtn = array_merge($rtn, $result_default);
			$rtn["success"] = true;
				
			$result_custom = $db->selectOne("settings_custom", "`key` = '" . $rtn["key"] . "' AND `owner` = '" . $idenifier . "'");
			if ($result_custom === false)
			{
				if (empty($idenifier2))
				{
					$rtn["isDefault"] = true;
				}
				else
				{
					$result_custom = $db->selectOne("settings_custom", "`key` = '" . $rtn["key"] . "' AND `owner` = '" . $idenifier2 . "'");
						
					if ($result_custom === false)
					{
						$rtn["isDefault"] = true;
					}
					else
					{
						$rtn["value"] = $result_custom["value"];
						$rtn["isDefault"] = false;
					}
				}
			}
			else
			{
				$rtn["value"] = $result_custom["value"];
				$rtn["isDefault"] = false;
			}
				
			getFramework()->getServer()->Debug2("ChioriSettings: Retrived \"" . $key . "\" setting from database using idenifier \"" . $idenifier . "\", Result: \"" . $rtn["value"] . "\"");
			return ($value_only) ? $rtn["value"] : $rtn;
		}
		
		function set($key, $value = "", $idenifier = -1) // Empty value deletes setting.
		{
			$users = getFramework()->getUserService();
			$db = getFramework()->getDatabaseEngine();
			
			$key = strtoupper($key);
				
			if ($idenifier == -1)
			{
				switch (substr($key, 0, 5))
				{
					case "TEXT_":
					case "LOCAT":
						$idenifier = $users->GetLocations(true);
						break;
					case "ACCOU":
						$idenifier = $users->GetAccounts(true);
						break;
					default:
						$idenifier = $users->CurrentUser["userID"];
				}
				if ($idenifier === false) $idenifier = "";
			}
				
			$result = $db->selectOne("settings_default", "`key` = '" . $key . "'");
				
			if ($result === false)
			{
				getFramework()->getServer()->Error("ChioriSettings: Retriving \"" . $key . "\" from database was unsuccessfull, Error: \"Non-existent Setting\"");
				return false;
			}
				
			if (empty($value) || $result["value"] == $value)
			{
				return $db->delete("settings_custom", "`key` = '" . $key . "' AND `owner` = '" . $idenifier . "'");
			}
			else
			{
				$result = $db->selectOne("settings_custom", "`key` = '" . $key . "' AND `owner` = '" . $idenifier . "'");
				if ($result !== false)
				{
					return $db->update("settings_custom", array("value" => $value), "`key` = '" . $key . "' AND `owner` = '" . $idenifier . "'", 1);
				}
				else
				{
					return $db->insert("settings_custom", array("key" => $key, "value" => $value, "owner" => $idenifier));
				}
			}
		}
	}