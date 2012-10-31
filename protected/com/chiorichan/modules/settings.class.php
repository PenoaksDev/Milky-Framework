<?
	/**
	 * (C) 2012 Chiori Greene
	 * All Rights Reserved.
	 * Author: Chiori Greene
	 * E-Mail: chiorigreene@gmail.com
	 * 
	 * This class is intellectual property of Chiori Greene and can only be distributed in whole with its parent
	 * framework which is known as Chiori Framework.
	 * 
	 * Keep software like this free and open source by following the authors wishes.
	 * 
	 * Class Name: Settings
	 * Version: 1.0.0 Offical Release
	 * Released: May 6th, 2012
	 * Description: This class is used to retrive settings from a defined db table.
	 */

	class com_chiorichan_modules_settings extends ModuleTemplateBasic
	{
		public $chiori;
		private $objDB = "";
	
		function __construct ($parentClass, $config)
		{
			$this->chiori = $parentClass;
			
			if (empty($config["objDB"]))
				$config["objDB"] = "com.chiorichan.modules.db";
			
			$this->objDB = $this->chiori->nameSpaceGet($config["objDB"]);
			
			if ($this->objDB === false)
			{
				$this->chiori->Panic(500, "ChioriSettings: Unable to find a suitable db module to use.");
			}
		}
		
		function get($key, $idenifier = -1, $idenifier2 = "", $value_only = true, $default_value = "") // Returns setting string based on idenifier.
		{
			// Check if only one idenifier was provided. i.e. Called from a script not made for Version 2 of this subroutine.
			if (is_bool($idenifier2))
			{
				if (!is_bool($value_only))
					$default_value = $value_only;
				
				$value_only = $idenifier2;
				$idenifier2 = "";
				
				$this->chiori->Warning("Subroutine: \"settings->get\" called with old arguments pattern.");
			}
			
			$rtn = array("success" => false, "key" => strtoupper($key), "value" => $default_value);
			
			if ($idenifier == -1)
			{
				$idenifier = $this->chiori->nameSpaceGet("com.chiorichan.modules.users")->CurrentUser["userID"];
				if ($idenifier === false) $idenifier = "";
			}
			
			$result_default = $this->objDB->selectOne("settings_default", "`key` = '" . $rtn["key"] . "'");
			if ($result_default === false)
			{
				$this->chiori->Error("ChioriSettings: Retriving \"" . $key . "\" from database was unsuccessfull, Error: \"Non-existent Setting\", Returning default value of: \"" . $rtn["value"] . "\"");
				return ($value_only) ? $rtn["value"] : $rtn;
			}
			
			$rtn = array_merge($rtn, $result_default);
			$rtn["success"] = true;
			
			$result_custom = $this->objDB->selectOne("settings_custom", "`key` = '" . $rtn["key"] . "' AND `owner` = '" . $idenifier . "'");
			if ($result_custom === false)
			{
				if (empty($idenifier2))
				{
					$rtn["isDefault"] = true;
				}
				else
				{
					$result_custom = $this->objDB->selectOne("settings_custom", "`key` = '" . $rtn["key"] . "' AND `owner` = '" . $idenifier2 . "'");
					
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
			
			$this->chiori->Debug2("ChioriSettings: Retrived \"" . $key . "\" setting from database using idenifier \"" . $idenifier . "\", Result: \"" . $rtn["value"] . "\"");
			return ($value_only) ? $rtn["value"] : $rtn;
		}
		
		function set($key, $value = "", $idenifier = -1) // Empty value deletes setting.
		{
			$key = strtoupper($key);
			
			if ($idenifier == -1)
			{
				switch (substr($key, 0, 5))
				{
					case "TEXT_":
					case "LOCAT":
						$idenifier = $this->chiori->nameSpaceGet("com.chiorichan.modules.users")->GetLocations(true);
						break;
					case "ACCOU":
						$idenifier = $this->chiori->nameSpaceGet("com.chiorichan.modules.users")->GetAccounts(true);
						break;
					default:
						$idenifier = $this->chiori->nameSpaceGet("com.chiorichan.modules.users")->CurrentUser["userID"];
				}
				if ($idenifier === false) $idenifier = "";
			}
			
			$result = $this->objDB->selectOne("settings_default", "`key` = '" . $key . "'");
			
			if ($result === false)
			{
				$this->chiori->Error("ChioriSettings: Retriving \"" . $key . "\" from database was unsuccessfull, Error: \"Non-existent Setting\"");
				return false;
			}
			
			if (empty($value) || $result["value"] == $value)
			{
				return $this->objDB->delete("settings_custom", "`key` = '" . $key . "' AND `owner` = '" . $idenifier . "'");
			}
			else
			{
				$result = $this->objDB->selectOne("settings_custom", "`key` = '" . $key . "' AND `owner` = '" . $idenifier . "'");
				if ($result !== false)
				{
					return $this->objDB->update("settings_custom", array("value" => $value), "`key` = '" . $key . "' AND `owner` = '" . $idenifier . "'", 1);
				}
				else
				{
					return $this->objDB->insert("settings_custom", array("key" => $key, "value" => $value, "owner" => $idenifier));
				}
			}
		}
	}
?>