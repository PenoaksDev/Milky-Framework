<?php
	class UserService
	{
		protected $db;
		
		// Do Not Use Externally
		private $CurrentUser = array();
		
		function __construct()
		{
			$this->db = getFramework()->getConfig()->getDatabase();
		}
		
		public function getUserState () 
		{
			if ( is_null( $this->CurrentUser["valid"] ) )
				$this->CurrentUser["valid"] = false;
			
			return $this->CurrentUser["valid"];
		}
		
		public function getRank ( $userId = null, $rtn_title = false )
		{
			$accessId = $this->getString( "userlevel", null, $userId );
			
			if ( $accessId == null || empty( $accessId ) )
				return "";
			
			$rank = getFramework()->getDatabaseEngine()->selectOne("accounts_access", array("accessID" => $accessId));
			
			if ( $rtn_title )
				return $rank["title"];
			
			return $rank;
		}
		
		public function getString ( $key, $default = "", $userId = null )
		{
			if ( $userId == null )
			{
				$value = $this->CurrentUser[$key];
			}
			else
			{
				$value = "";
			}
			
			if ( $value == null )
				return $default;
			
			return $value;
		}
		
		public function getUserbyName ( string $userName )
		{
			if ( $userName == null || empty($userName) )
				return null;
			
			
			
		}
		
		function is_function($f) {
			return (is_string($f) && function_exists($f)) || (is_object($f) && ($f instanceof Closure));
		}
		
		public function getUserbyUID (string $uid)
		{
			if ( $uid == null || empty($uid) )
				return null;
				
				
				
		}
		
		public function initalize ( $reqlevel = -1 )
		{
			$username = $_REQUEST["user"];
			$password = $_REQUEST["pass"];
			$target = $_REQUEST["target"];
			
			if (isset($_REQUEST["logout"]))
			{
				$target = $_REQUEST["target"];
					
				$this->logout();
				
				if ( empty($target) )
				{
					$target = getFramework()->getConfigurationManager()->getString("scripts.login-form");
				}
				
				if ( empty($target) )
				{
					$target = "/accounts/login";
				}
					
				if ($target != $_SERVER["REQUEST_URI"])
					getFramework()->getServer()->dummyRedirect($target);
			}
			
			if (!empty($username) && !empty($password))
			{
				$msg = $this->validateLogin($username, $password);
				
				if ($msg["valid"])
				{
					$this->CurrentUser = $msg;
					
					if ( empty( $target ) )
					{
						$target = getFramework()->getConfigurationManager()->getString("scripts.login-post");
					}
					
					if ( empty( $target ) )
					{
						$target = "/panel";
					}
					
					getFramework()->getServer()->Info("Login Success: Username \"" . $username . "\", UserID \"" . $msg["userID"] . "\", Password \"" . $password . "\", Redirecting to \"" . $target . "\".");
					if (!empty($target)) getFramework()->getServer()->dummyRedirect($target);
				}
				else
				{
					$login = getFramework()->getConfigurationManager()->getString("scripts.login-form");
					
					if ( empty( $login ) )
					{
						$login = "/login";
					}
					
					getFramework()->getServer()->Warning("Login Failed: Username \"" . $username . "\", UserID \"" . $msg["userID"] . "\", Password \"" . $password . "\", Error Message \"" . $msg["msg"] . "\"");
					getFramework()->getServer()->dummyRedirect($login . "?msg=" . $msg["msg"] . "&target=" . $target . "&user=" . $username);
				}
			}
			else
			{
				$username = getFramework()->getServer()->getSessionString("User");
				$password = getFramework()->getServer()->getSessionString("Pass");
				$user = $this->checkLogin($username, $password);
				
				if ($user["valid"])
				{
					$this->CurrentUser = $user;
					getFramework()->getServer()->Info("Login Status: Username \"" . $user["username"] . "\", UserID \"" . $user["userID"] . "\", Name \"" . $user["displayname"] . "\", Rank Title \"" . $user["displaylevel"] . "\".");
				}
				else
				{
					getFramework()->getServer()->Info("Login Status: No valid login present.");
				}
					
				if ($reqlevel != -1)
				{
					if (!$this->GetPermission("", $user["userID"]) && $reqlevel == 0) // Root Check
					{
						getFramework()->getServer()->Panic(401, "This page is limited to Administrators only!");
					}
					elseif ($this->GetPermission($reqlevel, $user["userID"], $title) && $this->CurrentUser["userlevel"] != 0)
					{
						getFramework()->getServer()->Panic(401, "This page is limited to members with access to the \"" . $reqlevel . "\" permission or better.");
					}
					elseif (!$this->CurrentUser["valid"])
					{
						$target = getFramework()->getConfigurationManager()->getString("scripts.login-form");
						if ( empty($target) )
						{
							$target = "/accounts/login";
						}
							
						getFramework()->getServer()->Debug1("Login required, Redirecting to login page, Required Level \"" . $reqlevel . "\".");
						getFramework()->getServer()->dummyRedirect($target . "?msg=" . urlencode("You must be logged in to view that page!") . "&target=" . urlencode($_SERVER["SCRIPT_URI"]));
						return false;
					}
				}
			}
			
			return true;
		}
		
		private function loginFailed($user, $reason)
		{
			$db = getFramework()->getDatabaseEngine();
		
			if (!empty($user["username"]))
				$db->update("users", array("lastloginfail" => time(), "numloginfail" => $result["numloginfail"] + 1), "username='" . $user["username"] . "'");
		
			$user["msg"] = $reason;
		
			return $user;
		}
		
		function checkLogin($username, $password)
		{
			$obj = getFramework()->getDatabaseEngine();
			$cfg = getFramework()->getConfigurationManager();
		
			$msg = array(
					"emptyUsername" => "The specified username was empty. Please try again.",
					"emptyPassword" => "The specified password was empty. Please try again.",
					"incorrectLogin" => "Username and Password provided did not match any users on file.",
					"successLogin" => "Your login has been successfully authenticated.",
					"permissionsError" => "Fatal error was detected with your user permissions. Please notify an administrator ASAP."
			);
			
			$user = array(
					"username" => $username,
					"password" => $password,
					"userlevel" => -1,
					"valid" => false,
					"msg" => ""
			);
		
			if (empty($username) && empty($user["msg"])) $user["msg"] = $msg["emptyUsername"];
			if (empty($password) && empty($user["msg"])) $user["msg"] = $msg["emptyPassword"];
		
			/*
			$func = $cfg->getString("loginOverRide");
			if ($this->is_function($func))
			{
				$user = $func($username, $password);
					
				if ( !$user )
				{
					$user["msg"] = $msg["incorrectLogin"];
				}
			}
			*/
			
			if ( !empty( $user["msg"] ) )
				return $user;
			
			$users = array();
			foreach ($cfg->getArray("login-fields") as $field)
			{
				$users[] = "`" . $field . "` = '" . $username . "'";
			}
				
			$users = $obj->array2Where($users, "OR");
			
			/*
			$passs = array();
			foreach ($this->config["db"]["password_fields"] as $field)
			{
				$passs[] = "`" . $field . "` = '" . $password . "'";
			}
			*/
			
			$passs[] = "`password` = '" . $password . "'";
			$passs[] = "md5(`password`) = '" . $password . "'";
			
			$passs = $obj->array2Where($passs, "OR");
			
			$result = $obj->selectOne("users", "(" . $users . ") AND (" . $passs . ")");
			
			/*
			 * Several fields are expected from any hooks that are called for auth.
			 * 
			 * Passed fields: username, password
			 * 
			 * Expected fields: valid, userID, userlevel
			 * Optional fields: fname, name, displayname
			 * 
			 */
			
			if ( $result === false )
			{
				getFramework()->getServer()->Warning("&4Inital User Login Failed. Attempting Third-Party Hooks");
				
				$result = getFramework()->doHook( "auth", $user );
				
				if (is_array( $result )
					&& $result["valid"] === true
					&& isset( $result["userID"] )
					&& isset( $result["userlevel"] ))
				{
					$user = $result;
					$user["msg"] = "";
					$user["valid"] = true;
				}
				else
				{
					$user["msg"] = $msg["incorrectLogin"];
				}
			}
			
			if ( !empty( $user["msg"] ) )
				return $user;
			
			$user["msg"] = $msg["successLogin"];
			
			// Permissions
			$level = $obj->selectOne("accounts_access", array("accessID" => $result["userlevel"]));
			
			if ( $level === false )
			{
				$user["msg"] = $msg["permissionsError"];
				return $user["msg"];
			}
			
			$user["valid"] = true;
			
			$user = arrayJoin($user, $result);
			
			if ( empty( $result["displayname"] ) )
				$user["displayname"] = (empty($result["fname"])) ? $result["name"] : $result["fname"]." ".$result["name"];
			
			$user["displaylevel"] = $level["title"];
			
			$obj->update("users", Array("lastactive" => microtime(true)), "userID = '" . $result["userID"] . "'", 1);
			
			return $user;
		}
		
		function validateLogin($username, $password)
		{
			$obj = getFramework()->getDatabaseEngine();
			
			$msg = array(
					"accountNotActivated" => "Account is not activated.",
					"underAttackPleaseWait" => "Max fail login tries reached. Account locked for 30 minutes."
			);
			
			$user = $this->checkLogin($username, $password);
			
			if (!$user["valid"]) return $this->loginFailed($user, $user["msg"]);
			
			if ($user["numloginfail"] > 5)
			{
				if ($user["lastloginfail"] > (time() - 1800))
				{
					return $this->loginFailed($user, $response["underAttackPleaseWait"]);
				}
			}
			
			if ($user["actno"] != 0) return $this->loginFailed($user, $response["accountNotActivated"], $obj);
			
			$obj->update("users", Array("lastlogin"=>microtime(true)), "userID = '" . $user["userID"] . "'", 1);
			$obj->update("users", Array("numloginfail"=>0), "userID = '" . $user["userID"] . "'", 1);
			
			getFramework()->getServer()->setSessionString("User", $user["userID"]);
			getFramework()->getServer()->setSessionString("Pass", md5($password));
			
			if ( $_REQUEST["remember"] == true )
				getFramework()->getServer()->setCookieExpiry( 5 * 365 * 24 * 60 * 60 );
			else
				getFramework()->getServer()->setCookieExpiry( 604800 );
			
			return $user;
		}
		
		private function logout()
		{
			getFramework()->getServer()->destroySession();
			getFramework()->getServer()->Info("User Logged Out, Username \"" . $username . "\", Target URL \"" . $target . "\"");
		}
		
		public function DeletePermission($username, $perm_name)
		{
			/*
			 * This function deletes defined permision name from users permission level.
			* Warning: This function alters on the global scale so other users with the same level will also be affected.
			*/
		
		
		}
		
		public function UpdatePermission($username, $perm_name)
		{
			/*
			 * This function updates the permissions table so Current User has access to defined permision name.
			* Warning: This function alters on the global scale so other users with the same level will also be affected.
			*/
		
		
		}
		
		/*
		 * Checks the promissions of a current logged in user.
		* Returns false if no user is logged in.
		* Same as getPermission as in empty permmission name will do a root check.
		*/
		public function hasPermission ($perm_name)
		{
			if ( !$this->getUserState() )
				return false;
		
			return $this->GetPermission($perm_name, $this->CurrentUser["username"]);
		}
		
		public function GetPermission($perm_name = array(), $username = "", &$title = "") // Empty $perm_name would result in a Root Permissions Check.
		{
			/*
			 * This function checks the users permission level againts the permissions table for if the requested permission is allowed by Current User.
			*/
			$db = getFramework()->getDatabaseEngine();
			
			if (empty($perm_name)) $perm_name = array("ROOT"); // Set Requested Permision Name to ROOT if none was given.
			
			if (!is_array($perm_name)) $perm_name = explode("|", $perm_name);
			
			if (empty($username)) // Get Currently Open User Data if none specified.
			{
				$username = $this->CurrentUser["username"];
				$userlevel = $this->CurrentUser["userlevel"];
			}
			
			if ($userlevel == null)
			{
				$user = $db->selectOne("users", "username = '" . $username . "'"); // Retrieve User Information.
				if (count($user) < 1) return false; // Return false is there was an error retrieving users information.
				$userlevel = $user["userlevel"];
			}
			
			$perm = $db->selectOne("accounts_access", "accessID = '" . $userlevel . "'"); // Retrive Permissions from SQL.
			if (count($perm) < 1) return false; // Return false if there was an error retrieving Permissions from SQL.
			
			$perm_list = explode("|", $perm["permissions"]); // Explode permissions into array.
			
			$title = $perm["title"]; // Set &$title string with user level title.
			
			if (in_array("ROOT", $perm_list)) return true; // Return true if user has ROOT Permissions.
			if (in_array("ADMIN", $perm_list) && !in_array("ROOT", $perm_name)) return true; // Return true if user has ADMIN Permissions and anything other then ROOT is being requested.
			
			foreach($perm_name as $val)
			{
				$granted = false;
				$perm_sub = explode("&", $val);
				
				foreach($perm_sub as $perm)
				{
					if ( substr( $perm, 0, 1) == "!" ) // Reverse check - NOT
					{
						if (in_array(substr( $perm, 1), $perm_list))
						{
							$granted = false;
							break;
						}
						else
						{
							$granted = true;
						}
					}
					else
					{
						if (in_array($perm, $perm_list))
						{
							$granted = true;
						}
						else
						{
							$granted = false;
							break;
						}
					}
				}
				
				if ( $granted )
					return true; // Return true if one of the requested permission names exists in users allowed permissions list.
			}
			
			return false;
		}
		
		function CheckPermision($perm_name = "")
		{
			/*
			 * This function gives scripts easy access to the GetPermission function without the extra requirments.
			* Recommended uses would be checking if page load is allowed by user.
			*/
		
			if (!$this->GetPermission($perm_name))
			{
				echo("<h1>Unauthorized</h1>");
				echo("<p class=\"warning\">This page is limited to members with access to the \"" . $perm_name . "\" permission or better. If access is required please contact us or see your account holder for help.</p>");
				die();
			}
		}
		
		function GetMyLocations($rtn_one = false, $rtn_str = false, $where_alt = "")
		{
			$db = getFramework()->getDatabaseEngine();
			
			if ($this->GetPermission("ADMIN"))
			{
				$where = "";
			}
			else
			{
				$where = array();
				$result_acc = $db->select("accounts", "maintainers like '%" . $this->CurrentUser["userID"] . "%'");
				
				if (count($result_acc) > 0)
				{
					foreach ($result_acc as $row_acc)
					{
						$where[] = "acctID = '" . $row_acc["acctID"] . "'";
					}
				}
				
				$result_acc = $db->select("locations", "maintainers like '%" . $this->CurrentUser["userID"] . "%'");
				if (count($result_acc) > 0)
				{
					foreach ($result_acc as $row_acc)
					{
						$where[] = "locID = '" . $row_acc["locID"] . "'";
					}
				}
				
				if ( $this->GetPermission("STORE") )
					$where[] = "locID = '" . $this->getString("userID") . "'";
				
				$where = $db->array2Where($where, "OR");
				if ( empty($where) ) return false;
			}
				
			if (!empty($where_alt))
			{
				if (is_array($where_alt)) $where_alt = $db->WhereArray($where);
				$where = $db->array2Where(array("(" . $where . ")", "(" . $where_alt . ")"));
			}
				
			if ($rtn_one || $rtn_str)
			{
				$result = $db->selectOne("locations", $where);
				if ($rtn_one) return $result;
				if ($rtn_str) return $result["locID"];
			}
			
			return $db->Select("locations", $where);
			
			getFramework()->getServer()->Debug1("[UserServices] Returning authorized locations array from database.");
		}
		
		public function GetMyAccounts($acctNumbersOnly = false, $rtn_one = false, $rtn_str = false)
		{
			$db = getFramework()->getDatabaseEngine();
			
			if ($this->GetPermission("ADMIN"))
			{
				$where = "";
			}
			else
			{
				$where = "maintainers like '%" . $this->CurrentUser["userID"] . "%'";
			}
			
			$myAccounts = $db->select("accounts", $where, array(), CONFIG_SITE);
			getFramework()->getServer()->Debug1("[UserServices] Returning authorized accounts array from database.");
			
			if ( $acctNumbersOnly )
			{
				$accounts = $myAccounts;
				$myAccounts = array();
				foreach ( $accounts as $acct )
				{
					$myAccounts[] = $acct["acctID"];
				}
			}
			
			if ($rtn_one)
			{
				if (count($myAccounts) > 0)
				{
					return $myAccounts[0];
				}
				else
				{
					return array();
				}
			}
			else
			{
				return $myAccounts;
			}
		}
	}
