<?php
	Class Auth extends Hook
	{
		public function __construct($config = null)
		{
			parent::__construct();
			$this->pluginName = "DDNMG Custom Auth Hook";
			$this->pluginPackage = "com.ddnmg.hooks.auth";
		}
		
		public function customHooks ()
		{
			return array("custom1");
		}
		
		public function auth ( $parms = array() )
		{
			$obj = getFramework()->getDatabaseEngine();
			$users = array();
			$passs = array();
			
			$users[] = "`locID` = '" . $parms["username"] . "'";
			$users[] = "`login` = '" . $parms["username"] . "'";
			
			$users = $obj->array2Where($users, "OR");
				
			$passs[] = "`password` = '" . $parms["password"] . "'";
			$passs[] = "md5(`password`) = '" . $parms["password"] . "'";
				
			$passs = $obj->array2Where($passs, "OR");
			
			$result = getFramework()->getDatabaseEngine()->selectOne( "locations", "(" . $users . ") AND (" . $passs . ")" );
			
			if ( $result["logins_allowed"] == 1 )
			{
				return array(
						"valid" => true,
						"userID" => $result["locID"],
						"password" => $result["password"],
						"userlevel" => "location",
						"displayname" => $result["title"],
						"email" => $result["login"],
				);
			}
		}
		
		public function custom1 ( $parms = array() )
		{
			
		}
	}