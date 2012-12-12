<?
class com_chiorichan_modules_users
{
	/* Space Cache Varables */
	private $myLocations = null;
	private $myAccounts = null;
	
	/* Module Varables */
	public $CurrentUser = array();
	public $cliori;
	public $config = Array( /* Default Module Configuration. Do not change value here. Change them within your site loader. */
		"RootUser" => "cg092m", /* Database user used for all root operations. May become obsolete in future. */
		"db" => array( /* Database configuration */
			"table" => "users", /* Which table to check for logins against. */
			"username_fields" => "username", /* Username fields found within database. ex: username, userID, phone or email */
			"password_fields" => "password" /* Password fields found within database. Should not be ever changed but hey why not.*/
			),
		"functions" => array( /* Anonymous/Closure Functions => function () {} */
			"loginOverRide" => null, /* Function which overrides default login subroutine, Return true to authorize user login. Arguments provided are user and pass. */
			"loginPost" => null, /* Function called after user login authorized. First argument true if login was a success. */
			"loginPre" => null, /* Function called before user login authorized. */
			"logoutPre" => null, /* Function called before user session data is removed. */
			"logoutPost" => null, /* Function called after user session data is removed. */
			"pageLoad" => null /* Function called upon each pageload. */
			),
		"scripts" => array(
			"login-form" => "/login.php", /* Form where login details are provided. */
			"login-post" => "/", /* Where to direct all successful logins. */
			"logout" => "?logout" /* Where to direct logouts. Obsolete? */
			)
		);

	function __construct ($parentClass, $conf = array())
	{
		$this->chiori = $parentClass;
		$this->config = arrayMerge($this->config, $conf);
	}

	function is_function($f) {
	    return (is_string($f) && function_exists($f)) || (is_object($f) && ($f instanceof Closure));
	}

	public function PageLoad ($reqlevel = -1)
	{
		$username = $_POST["user"];
		$password = $_POST["pass"];
		$remember = ($_POST["remember"] == 1 || $_POST["remember"] == "true");
		$target = $_POST["target"];
		
		if (isset($_GET["logout"]))
		{
			$target = $_GET["target"];
			
			$this->logout();
			
			if (isset($this->config["scripts"]["login-form"]) && empty($target))
			{
				$target = $this->config["scripts"]["login-form"];
			}
			elseif (empty($target))
			{
				$target = "/accounts/login";
			}
			
			if ($target != $_SERVER["REQUEST_URI"])	$this->chiori->nameSpaceGet("com.chiorichan.modules.functions")->dummyRedirect($target);
		}
		
		if (!empty($username))
		{
			$msg = $this->validateLogin($username, $password, $remember);
			
			if ($msg["valid"])
			{
				$this->CurrentUser = $msg;
				
				if (empty($target) && isset($this->config["scripts"]["login-post"]))
				{
					$target = $this->config["scripts"]["login-post"];
				}
				elseif (empty($target))
				{
					$target = "/panel";
				}
				
				$this->chiori->Info("Login Success: Username \"" . $username . "\", UserID \"" . $msg["userID"] . "\", Password \"" . $password . "\", Redirecting to \"" . $target . "\".");
				if (!empty($target)) $this->chiori->com->chiorichan->modules->functions->dummyRedirect($target);
			}
			else
			{
				if (empty($this->config["scripts"]["login-form"]))
				{
					$login = "/login";
				}
				else
				{
					$login = $this->config["scripts"]["login-form"];
				}
				
				$this->chiori->Warning("Login Failed: Username \"" . $username . "\", UserID \"" . $msg["userID"] . "\", Password \"" . $password . "\", Error Message \"" . $msg["msg"] . "\"");
				$this->chiori->com->chiorichan->modules->functions->dummyRedirect($login . "?msg=" . $msg["msg"] . "&target=" . $target . "&user=" . $username);
			}
		}
		else
		{
			if (isset($this->chiori->com->chiorichan->modules->sessions))
			{
		        $username = $this->chiori->com->chiorichan->modules->sessions->GetValue("User");
		        $password = $this->chiori->com->chiorichan->modules->sessions->GetValue("Pass");
			}
			else
			{
				session_start();
				
				$username = $_SESSION["User"];
				$password = $_SESSION["Pass"];
			}
			
	        $user = $this->checkLogin($username, $password);
	        
	        if ($user["valid"])
	        {
	        	$this->CurrentUser = $user;
	        	$this->chiori->Info("Login Status: Username \"" . $user["username"] . "\", UserID \"" . $user["userID"] . "\", Name \"" . $user["displayname"] . "\", Rank Title \"" . $user["displaylevel"] . "\".");
	        }
	        else
	        {
		        $this->chiori->Info("Login Status: No valid login present.");
	        }
			
	        if ($reqlevel != -1)
	        {
	        	if (!$this->GetPermission("", $user["userID"]) && $reqlevel == 0) // Root Check
                {
					$this->chiori->Panic(401, "This page is limited to Administrators only!");
                }
                elseif ($this->GetPermission($reqlevel, $user["userID"], $title) && $this->CurrentUser["userlevel"] != 0)
                {
					$this->chiori->Panic(401, "This page is limited to members with access to the \"" . $reqlevel . "\" permission or better.");
                }
                elseif (!$this->CurrentUser["valid"])
                {
					if (isset($this->config["scripts"]["login-form"]))
					{
						$target = $this->config["scripts"]["login-form"];
					}
					else
					{
						$target = "/accounts/login";
					}
					
					$this->chiori->Debug1("Login required, Redirecting to login page, Required Level \"" . $reqlevel . "\".");
					$this->chiori->com->chiorichan->modules->functions->dummyRedirect($target . "?msg=" . urlencode("You must be logged in to view that page!") . "&target=" . urlencode($_SERVER["SCRIPT_URI"]));
					return false;
				}
			}
		}
		
		return true;
	}
	
	private function loginFailed($user, $reason, $obj = "")
	{
		if (empty($obj)) $obj = $this->chiori->db;
		
		if (!empty($user["username"]))
		$obj->update("users", array("lastloginfail" => time(), "numloginfail" => $result["numloginfail"] + 1), "username='" . $user["username"] . "'");
		
		$user["msg"] = $reason;
		
		return $user;
	}
	
	function checkLogin($username, $password, $obj = "")
	{
		if (empty($obj)) $obj = $this->chiori->com->chiorichan->modules->db;
		
		$msg = array(
			"emptyUsername" => "The specified username was empty. Please try again.",
			"emptyPassword" => "The specified password was empty. Please try again.",
			"incorrectLogin" => "Username and Password provided did not match any users on file.",
			"permissionsError" => "Fatel error was detected with your user permissions. Please notify an administrator ASAP."
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
		
		$func = $this->config["functions"]["loginOverRide"];
		if ($this->is_function($func))
		{
			$user = $func($username, $password);
			
			if ( !$user )
			{
				$user["msg"] = $msg["incorrectLogin"];
			}
		}
		else
		{
			if (!is_array($this->config["db"]["username_fields"]))
				$this->config["db"]["username_fields"] = explode("|", $this->config["db"]["username_fields"]);
				
			if (!is_array($this->config["db"]["password_fields"]))
				$this->config["db"]["password_fields"] = explode("|", $this->config["db"]["password_fields"]);
			
			$users = array();
			foreach ($this->config["db"]["username_fields"] as $field)
			{
				$users[] = "`" . $field . "` = '" . $username . "'";
			}
			
			$users = $this->chiori->nameSpaceGet("com.chiorichan.modules.db")->array2Where($users, "OR");
			
			$passs = array();
			foreach ($this->config["db"]["password_fields"] as $field)
			{
				$passs[] = "`" . $field . "` = '" . $password . "'";
			}
			
			$passs = $this->chiori->nameSpaceGet("com.chiorichan.modules.db")->array2Where($passs, "OR");
			
			$result = $obj->selectOne($this->config["db"]["table"], "(" . $users . ") AND (" . $passs . ")");
			
			if ($result === false && empty($user["msg"])) $user["msg"] = $msg["incorrectLogin"];
			
			if (md5($result["password"]) != $password && $result["password"] != $password && empty($user["msg"]))
				$user["msg"] = $msg["incorrectLogin"];
		}
		
		$level = $this->chiori->nameSpaceGet("com.chiorichan.modules.db")->selectOne("accounts_access", array("accessID" => $result["userlevel"]));
		
		if ($level === false && empty($user["msg"]))
			$user["msg"] = $msg["permissionsError"];
		
		if (empty($user["msg"]))
		{
			$user["valid"] = true;
		}
		else
		{
			return $user;
		}
		
		$user = array_merge($user, $result);
		$user["displayname"] = (empty($result["fname"])) ? $result["name"] : $result["fname"]." ".$result["name"];
		$user["displaylevel"] = $level["title"];
		
		$obj->update("users", Array("lastactive" => microtime(true)), "userID = '" . $result["userID"] . "'", 1);
		
		return $user;
	}
	
	function validateLogin($username, $password, $remember = false, $obj = "")
	{
		if (empty($obj)) $obj = $this->chiori->com->chiorichan->modules->db;
		
		$msg = array(
			"accountNotActivated" => "Account is not activated.",
			"underAttackPleaseWait" => "Max fail login tries reached. Account locked for 30 minutes."
			);
		
		$user = $this->checkLogin($username, $password, $obj);
		
		if (!$user["valid"]) return $this->loginFailed($user, $user["msg"], $obj);
		
		if ($user["numloginfail"] > 5)
		{
			if ($user["lastloginfail"] > (time() - 1800))
			{
				return $this->loginFailed($user, $response["underAttackPleaseWait"], $obj);
			}
		}
		
		if ($user["actno"] != 0) return $this->loginFailed($user, $response["accountNotActivated"], $obj);
		
		$obj->update("users", Array("lastlogin"=>microtime(true)), "userID = '" . $user["userID"] . "'", 1);
		$obj->update("users", Array("numloginfail"=>0), "userID = '" . $user["userID"] . "'", 1);
		
		$remember = ($remember) ? time() + 604800 : time() + 86400;
		
		if (isset($this->chiori->com->chiorichan->modules->sessions)) /* Check if sessions module is present. */
		{
			$this->chiori->com->chiorichan->modules->sessions->setArray(Array("User" => $user["userID"], "Pass" => $password));
		}
		else
		{
			session_start();
			
			$_SESSION["User"] = $user["userID"];
			$_SESSION["Pass"] = $password;
		}
		
		return $user;
	}

	private function logout()
	{
		if (isset($this->chiori->com->chiorichan->modules->sessions))
		{
	        $this->chiori->com->chiorichan->modules->sessions->SetArray(Array("User" => "", "Pass" => ""));
		}
		else
		{
			session_start();
			
			$username = $_SESSION["User"];
			$_SESSION["User"] = "";
			$_SESSION["Pass"] = "";
		}
		
		$this->chiori->Info("User Logged Out, Username \"" . $username . "\", Target URL \"" . $target . "\"");
	}
	
	public function LevelCheck ($reqlevel = -1)
	{
	        if ($reqlevel != -1)
	        {
	                $this->chiori->Debug1("Page Level Requirment, Required level \"" . $reqlevel . "\".");
	                
	                if ($reqlevel == 0 && $this->CurrentUser["userlevel"] > 0)
	                {
                        $this->chiori->events->SendError("This page is limited to Administrators only!");
	                }
	                else
	                {
	                        if ($this->CurrentUser["userlevel"] < $reqlevel && $this->CurrentUser["userlevel"] > 0)
	                        {
                                $this->chiori->events->SendError("This page is limited to members with access level " . $reqlevel . " or higher, Your level " . $CurrentUser["userlevel"] . ".");
	                        }
	                }
	        }
	}

	public function CreateUser ($RecordArr)
	{
		$RecordArr["password"] = $RecordArr["password1"];
		unset($RecordArr["password1"]);
		unset($RecordArr["password2"]);
		unset($RecordArr["human"]);
		unset($RecordArr["agree"]);
		$RecordArr["birthdate"] = $RecordArr["birthdate3"] . "." . $RecordArr["birthdate1"] . "." . $RecordArr["birthdate2"];
		unset($RecordArr["birthdate1"]);
		unset($RecordArr["birthdate2"]);
		unset($RecordArr["birthdate3"]);

		$this->cliori->db->insert("users", $RecordArr);
	}

	public function RequireLogin ($RequireLevel = 1)
	{
		if (!$this->validlogin($RequireLevel))
		{
			$this->chiori->misc->DummyProofRedirect($this->config["scripts"]["login-form"] . "?target=" . $_SERVER["REQUEST_URI"]);
		}
	}

	public function isOnline ($userrow, $returnbool = false)
	{
		if ((time()-$userrow['lastactive'])<=600)
		{
			if ($returnbool) return true;
			echo ("<img src=\"/gpjsites/images/icon_user.gif\" style=\"margin: 0px;\" alt=\"User Online\" /> Online");
		}
		else
		{
			if ($returnbool) return false;
			echo ("<img src=\"/gpjsites/images/icon_user_inactive.gif\" style=\"margin: 0px;\" alt=\"User Offline\" /> Offline");	
		}
	}

	public function UserImage ($arguments  = "", $userID = "")
	{
		if (empty($userID))
		{
			$imgID = $this->CurrentUser["imgID"];
		}
		else
		{
			$result = $this->chiori->db->selectOne("users", "userID = '" . $userID . "'");
			if ($result === false) return false;
			$imgID = $result["imgID"];
		}

		if ($this->chiori->db->Select("images", "imgID = '" . $imgID . "'") !== false)
		{
			return "/images/" . $imgID . "_thumb_100_0.jpg" . $arguments;
		}
		elseif ($this->chiori->mysql->SelectTable("avatar", "avatarID = '" . $imgID . "'") !== false)
		{
			return "/images/avatar/" . $imgID . ".jpg" . $arguments;
		}
		else
		{
			return "/images/no_image.png"; // No Image
		}
	}

	public function validlogin($userlevel = 1)
	{
		if ($userlevel == -1) return true;
		if ($this->CurrentUser["valid"])
		{
			if ($this->CurrentUser["userlevel"] == 0 || $this->CurrentUser["userlevel"] >= $userlevel)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
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
		if ( $this->CurrentUser["username"] == null || empty($this->CurrentUser["username"]) )
			return false;
		
		return $this->GetPermission($perm_name, $this->CurrentUser["username"]);
	}
	
	public function GetPermission($perm_name = array(), $username = "", &$title = "") // Empty $perm_name would result in a Root Permissions Check.
	{
		/*
		 * This function checks the users permission level againts the permissions table for if the requested permission is allowed by Current User.
		 */
		
		if (empty($perm_name)) $perm_name = array("ROOT"); // Set Requested Permision Name to ROOT if none was given.
		
		if (!is_array($perm_name)) $perm_name = array($perm_name);
		
		if (empty($username)) // Get Currently Open User Data if none specified.
		{
			$username = $this->CurrentUser["username"];
			$userlevel = $this->CurrentUser["userlevel"];
		}
		
		if ($userlevel == null)
		{
			$user = $this->chiori->com->chiorichan->modules->db->selectOne("users", "username = '" . $username . "'"); // Retrieve User Information.
			if (count($user) < 1) return false; // Return false is there was an error retrieving users information.
			$userlevel = $user["userlevel"];
		}
		
		$perm = $this->chiori->com->chiorichan->modules->db->selectOne("accounts_access", "accessID = '" . $userlevel . "'"); // Retrive Permissions from SQL.
		if (count($perm) < 1) return false; // Return false if there was an error retrieving Permissions from SQL.
		
		$perm_list = explode("|", $perm["permissions"]); // Explode permissions into array.
		
		$title = $perm["title"]; // Set &$title string with user level title.
		
		if (in_array("ROOT", $perm_list)) return true; // Return true if user has ROOT Permissions.
		if (in_array("ADMIN", $perm_list) && !in_array("ROOT", $perm_name)) return true; // Return true if user has ADMIN Permissions and anything other then ROOT is being requested.
		
		foreach($perm_name as $val)
		{
			if (in_array($val, $perm_list))
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
	
	function authorizedLocation ($locID)
	{
		$places = array();
		$myLocations = $this->GetMyLocations();
		if ($myLocations === false) return false;
		foreach ($myLocations as $place)
		{
			if ($place["locID"] == $locID)
				return true;
		}
		return false;
	}
	
	function authorizedAccount ($acctID)
	{
		$places = array();
		$myAccounts = $this->GetMyAccounts();
		if ($myAccounts === false) return false;
		foreach ($myAccounts as $acct)
		{
			if ($acct["acctID"] == $acctID)
				return true;
		}
		return false;
	}
	
	function GetMyLocations($rtn_one = false, $rtn_str = false, $where_alt = "", $force_refresh = false)
	{
		if (!is_null($this->myLocations) && $force_refresh === false)
		{
			$this->chiori->Debug1("Users: Returning authorized locations array from cache");
			return $this->myLocations;
		}
		else
		{
			if ($this->GetPermission("ADMIN"))
			{
				$where = "";
			}
			else
			{
				$where = array();
				
				$result_acc = $this->chiori->nameSpaceGet("com.chiorichan.modules.db")->select("accounts", "maintainers like '%" . $this->CurrentUser["userID"] . "%'");
				if (count($result_acc) > 0)
				{
					foreach ($result_acc as $row_acc)
					{
						$where[] = "acctID = '" . $row_acc["acctID"] . "'";
					}
				}
				
				$result_acc = $this->chiori->nameSpaceGet("com.chiorichan.modules.db")->select("locations", "maintainers like '%" . $this->CurrentUser["userID"] . "%'");
				if (count($result_acc) > 0)
				{
					foreach ($result_acc as $row_acc)
					{
						$where[] = "locID = '" . $row_acc["locID"] . "'";
					}
				}
				
				$where = $this->chiori->nameSpaceGet("com.chiorichan.modules.db")->array2Where($where, "OR");
				
				if (empty($where)) return false;
			}
			
			if (!empty($where_alt))
			{
				if (is_array($where_alt)) $where_alt = $this->chiori->nameSpaceGet("com.chiorichan.modules.db")->WhereArray($where);
				$where = $this->chiori->nameSpaceGet("com.chiorichan.modules.db")->array2Where(array("(" . $where . ")", "(" . $where_alt . ")"));
			}
			
			if ($rtn_one || $rtn_str)
			{
				$result = $this->chiori->nameSpaceGet("com.chiorichan.modules.db")->selectOne("locations", $where);
				if ($rtn_one) return $result;
				if ($rtn_str) return $result["locID"];
			}
			
			$this->chiori->Debug1("Users: Returning authorized locations array from database");
			$this->myLocations = $this->chiori->nameSpaceGet("com.chiorichan.modules.db")->Select("locations", $where);
			return $this->myLocations;
		}
	}
	
	public function GetMyAccounts($rtn_one = false, $rtn_str = false)
	{
		if (!is_null($this->myAccounts) && $force_refresh === false)
		{
			$this->chiori->Debug1("Users: Returning authorized accounts array from cache");
		}
		else
		{
			if ($this->GetPermission("ADMIN"))
			{
				$where = "";
			}
			else
			{
				$where = "maintainers like '%" . $this->CurrentUser["userID"] . "%'";
			}
			
			$this->chiori->Debug1("Users: Returning authorized accounts array from database");
			$this->myAccounts = $this->chiori->nameSpaceGet("com.chiorichan.modules.db")->select("accounts", $where);
		}
		
		if ($rtn_one)
		{
			if (count($this->myAccounts) > 0)
			{
				return $this->myAccounts[0];
			}
			else
			{
				return false;
			}
		}
		else
		{
			return $this->myAccounts;
		}
	}

	public function GenerateAccountPane()
	{
	?>
		<div class="block">
			<h2>My Account</h2>
	  		<?PHP if ($this->validlogin()) { ?>
			<img src="<? echo($this->chiori->users->UserImage()) ?>" alt="User Image" />
	  		<span class="color_midori">Welcome Back,<br />
	  		<?PHP echo($this->CurrentUser["displayname"]); ?><br />
	  		Not You? <a href="/accounts/login">Login HERE.</a></span>
	  		<br />

	  		<ul>
	  			<li><a href="/accounts"><span>&gt; </span><b>Your Profile</b></a></li>
	  			<li><a href="/accounts/login?logout"><span>&gt; </span>Logout</a></li>
	  		</ul>
	  		<?PHP } else { ?>
	  		<ul>
	  			<li><span class="color_midori">Not logged in,</span></li>
	  			<li><a href="/accounts/login"><span>&gt; </span>Login Here.</a></li>
	  		</ul>
	  		<?PHP } ?>
		</div>
	<?
	}
	
	public function ManageActivity($userarr = Array())
	{
		$update = Array(
			"userid" => $userarr["userid"],
			"username" => $userarr["username_raw"],
			"ip" => $_SERVER['REMOTE_ADDR'],
			"last_active" => time(),
			"first_visit" => time()
		);

		$UserRecord = $this->db->Select("activity", "ip = '" . $_SERVER['REMOTE_ADDR'] . "'");
		if ($UserRecord === false)
		{
			$this->db->insert("activity", $update);
		}
		else
		{
			if (!empty($userarr["userid"]) && (empty($UserRecord["userid"]) || $UserRecord["userid"] != $userarr["userid"]))
			{
				$this->db->update("activity", Array("userid" => $userarr["userid"], "username" => $userarr["username_raw"]), "ip = '" . $_SERVER['REMOTE_ADDR'] . "'", 1);
			}

			$history = $UserRecord["history"] . "|" . $_SERVER['REQUEST_URI'];
			if (substr($history, 0, 1) == "|") $history = substr($history, 1);
		}

		$this->db->update("activity", Array("last_active" => time(), "last_page" => $_SERVER['REQUEST_URI'], "history" => $history, "useragent" => $_SERVER['HTTP_USER_AGENT']), "ip = '" . $_SERVER['REMOTE_ADDR'] . "'", 1);
	}
}
?>
