<?
	/*
	 * (C) 2011 Chiori Greene
	 * All Rights Reserved.
	 * Author: Chiori Greene
	 * E-Mail: chiori09@att.net
	 * 
	 * This class is intellectual property of Chiori Greene and can only be distributed in whole with its parent
	 * framework which is known as Chiori Framework. If you wish to alter and redistribute any part of it we
	 * require you make the changes by extending the class within the extension file.
	 * 
	 * Keep software like this free and open source by following the authors wishes.
	 * 
	 * Class Name: Chiori Sessions
	 * Version: 1.0.0 Offical Release
	 * Released: April 21, 2011
	 * Description: This class is used to give the framework access to a much more secure alternative to the
	 * builtin PHP sessions.
	 */

	class com_chiorichan_modules_sessions extends ModuleTemplateBasic
	{
		public $chiori;
		public $lifetime = 604800; // 604800 = 7 Days
		public $default_lifetime = 43200; // 43200 = 12 hours
		private $data = array();
		private $objDB = "";
		private $domain;
	
		function __construct ($parentClass, $config)
		{
			$this->chiori = $parentClass;
			
			if (empty($config["objDB"]))
			{
				$this->objDB = $this->chiori->fw->db;
			}
			else
			{
				$this->objDB = $config["objDB"];
			}
			
			$this->domain = $config["domain"];
			$domain = explode(".", $_SERVER["SERVER_NAME"]);
			if (empty($this->domain)) $this->domain = $domain[count($domain) - 1] . "." . $domain[count($domain)];
			
			$this->__ManageSessions();
			
			if (isset($_COOKIE["ChioriSessions"]))
			{
				$this->chiori->Info("ChioriSessions: Using session \"" . $_COOKIE["ChioriSessions"] . "\" for remote address " . $_SERVER["REMOTE_ADDR"]);
				
				$SessID = $_COOKIE["ChioriSessions"];
				$this->data["SessID"] = $SessID;
				$this->__GetSession($SessID);
			}
			else
			{
				$this->CreateSession();
			}
		}
		
		function SetArray($Array = Array())
		{
			$this->chiori->Info("Session Data Updated: " . var_export($Array, true));
			$this->data = array_merge($this->data, $Array);
			$this->__SetSession($this->data["SessID"]);
		}
		
		function GetValue($var)
		{
			return $this->data[$var];
		}
		
		function ClearValue($var)
		{
			$this->chiori->Info("ChioriSessions: Unsetting sessions varable \"" . $var . "\"");
			$this->data = array_merge($this->data, array($var => ""));
			$this->__SetSession($this->data["SessID"]);
			
			return true;
		}
		
		function SetValue($var, $val = "")
		{
			$this->chiori->Info("ChioriSessions: Updated sessions varable \"" . $var . "\" to \"" . $val . "\"");
			$this->data = array_merge($this->data, array($var => $val));
			$this->__SetSession($this->data["SessID"]);
			
			return true;
		}
		
		private function __SetSession($SessID)
		{
			$data = $this->data;
			unset($data["SessID"]);
			$this->objDB->update("sessions", array("data" => serialize($data)), "SessID = '" . $SessID . "'");
			unset($data);
		}
		
		private function __GetSession($SessID)
		{
			$array = $this->objDB->selectOne("sessions", "sessid = '" . $SessID . "'");
			
			if ($array === false)
			{
				$this->CreateSession($SessID);
				$array = $this->objDB->selectOne("sessions", "sessid = '" . $SessID . "'");
			}
			else
			{
				$expires = time() + $this->default_lifetime;
				$this->objDB->update("sessions", array("expires" => $expires), "sessid = '" . $SessID . "'");
			}
			
			if (!empty($array["data"]))
			{
				$this->data = array_merge($this->data, unserialize($array["data"]));
			}
			
			$this->data["SessID"] = $SessID;
		}
		
		private function __ManageSessions()
		{
			$this->objDB->delete("sessions", "`expires` < '" . time() . "'");
		}
		
		function CreateSession($SessID = "")
		{
			if (empty($SessID))
			{
				$allowed_chars = array ("1","2","3","4","5","6","7","8","9","0","a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z");
				for ($i = 1; $i <= 128; $i++) {
					$SessID = $SessID . $allowed_chars[mt_rand (0, count($allowed_chars)-1)];
				}
				
				$SessID = md5($SessID);
			}
			
			$Expires = time() + $this->lifetime;
			
			setcookie("ChioriSessions", $SessID, $Expires, "/", "." . $this->domain);
			
			unset($this->data);
			$this->data["IP"] = $_SERVER["REMOTE_ADDR"];
			
			$this->objDB->insert("sessions", array("sessid" => $SessID, "expires" => $Expires, "data" => serialize($this->data)));
			
			$this->data["SessID"] = $SessID;
			
			$this->chiori->Info("ChioriSessions: Created session \"" . $SessID . "\" for remote address " . $_SERVER["REMOTE_ADDR"]);
		}
		
		function DestroySession($SessID = "")
		{
			if (empty($SessID)) $SessID = $this->data["SessID"];
		
			setcookie("ChioriSessions", "", mktime(12,0,0,1, 1, 1990), "/", "." . $this->chiori->config["domain"]);
			$this->objDB->delete("sessions", "sessid = '" . $SessID . "'");
		}

	}
?>
