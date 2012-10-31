<?
	/**
	 * Name: Chiori Framework API
	 * Version: 4.4.0901 (Rainbow Dash)
	 * Last Updated: September 1st, 2012
	 *
	 * (C) 2012 Chiori Greene
	 * All Rights Reserved.
	 * @Author: Chiori Greene
	 * @E-Mail: chiorigreene@gmail.com
	 * @Website: http://web.chiorichan.com
	 * @Open Source License: GNU Public License Version 2
	 * 
	 * This code is intellectual property of Chiori Greene and can only be distributed in whole with its
	 * framework which is known as Chiori Framework.
	 *
	 * Description:
	 * This file is the sole framework controller to the Chiori Framework.
	 * On first use this framework needs no other files except this controller and be placed within
	 * a PHP writtable directory -- /protected recommmended. Each time this framework is initalized,
	 * this controller will attempt to automaticly download and update used components.
	 * 
	 * On a production server we recommend disabling automatic updating and downloading of components.
	 * But in exchange creating a cronjob to replace this need. See our wiki for instructions.
	 *
	 */
	
	/* Check if root site directory was set */
	defined ( "__ROOT__" ) or
		die ( "FATAL ERROR: __ROOT__ was not defined in Loader." );
	
	define ( "__BEGIN_TIME__", time() );
	define ( "__FW__", dirname(__FILE__) );
	define ( "DIRSEP", "/" );

	/* Make definition of extra log levels */
	define ( "LOG_DEBUG3", 7 );
	define ( "LOG_DEBUG2", 8 );
	define ( "LOG_DEBUG1", 9 );
	
	/* Define Yes and No as alternatives to True and False. */
	define ( "yes", true );
	define ( "no", false );
	
	/**
	 * Register ShutdownHandler function to be called on shutdown to handle
	 * some odd jobs. __classCaller is used as a proxy since register_shutdown_function
	 * can not call functions within a class object.
	 */
	register_shutdown_function("__classCaller", "ShutdownHandler");
	
	/**
	 * Special Function used to replace the not so great working array_merge.
	 * The built-in array_merge would only merge the first level of the array
	 * but would overwrite any sub arrays. This subroutine fixes that.
	 */
	function arrayMerge() {
	    if (func_num_args() < 2) {
	        trigger_error(__FUNCTION__ .' needs two or more array arguments', E_USER_WARNING);
	        return;
	    }
	    $arrays = func_get_args();
	    $merged = array();
	    
	    while ($arrays) {
	        $array = array_shift($arrays);
	        if (!is_array($array)) {
	            trigger_error(__FUNCTION__ .' encountered a non array argument', E_USER_WARNING);
	            return;
	        }
	        if (!$array)
	            continue;
	        foreach ($array as $key => $value)
	            if (is_string($key))
	                if (is_array($value) && array_key_exists($key, $merged) && is_array($merged[$key]))
	                    $merged[$key] = call_user_func(__FUNCTION__, $merged[$key], $value);
	                else
	                    $merged[$key] = $value;
	            else
	                $merged[] = $value;
	    }
	    return $merged;
	}
	
	/**
	 * Some built-in PHP functions are unable to call objected classes
	 * so this function is a backdoor to this limitation.
	 */
	function __classCaller($function, $args = "")
	{
		$chiori = fw();
		
		if ($chiori != null)
		{
			@$chiori->$function($args);
		}
	}
	
	/**
	 * Return Framework Objected Class from GLOBAL userspace.
	 * Notice: Make sure to set any varables by reference so to make sure any changes are preserved.
	 */
	function fw()
	{
		if (array_key_exists("chiori", $GLOBALS))
		{
			return $GLOBALS["chiori"];
		}
		else
		{
			return null;
		}
	}
	
	/* Module Template with configuration bank for use with Chiori Framework. */
	class ModuleTemplate
	{
		public $chiori;
		public $config = array();
		
		function __construct ($parentClass, $defaultConfig = array())
		{
			/* Save reference copy of parent class */
			$this->chiori =& $parentClass;
			
			/* Save configuration data */
			$this->config = array_merge($this->config, $defaultConfig);
		}
	}
	
	/* Module Template without configuration bank for use with Chiori Framework. */
	class ModuleTemplateBasic
	{
		public $chiori;
	
		function __construct ($parentClass)
		{
			// Save reference copy of parent class
			$this->chiori =& $parentClass;
		}
	}
	
	/**
	 * Dummy object class to take place of empty namespaces.
	 * ex: com, chiorichan, net and etc.
	 *
	 * Possible expansion needed for this class!!! i.e. Useful Function Calls.
	 */
	class classObject
	{
		public $fullNameSpace;
		
		function __construct($nameSpace)
		{
			$this->fullNameSpace = $nameSpace;
		}
	}
	
	/**
	 * Mane framework class.
	 */
	class ChioriFWBase
	{
		/**
		 * Pure Default Framework Configuration.
		 * This config is patched though bootstrap
		 * Needs expantion to protect some configuration varables. ie. version
		 */
		private $config = array(
			"debug" => array(
				"dump"				=>	-1,
				"sql"				=>	-1,
				"sql-table"			=>	null,
				"syslog"			=>	-1,
				"log"				=>	-1,
				"log-path"			=>	"logs/fw.log", // Path relative to fw root
				"log-date-format"	=>	"Y-m-d G:i:s",
				"log-on-error-only"	=>	true // Do not output to log unless something of importance was encountered.
				),
			"product" => "Chiori Framework",
			"version" => "4.4.0901 (Rainbow Dash)",
			"exception-handling" => true,
			"error-handling" => true,
			"domain" => "",
			"aliases" => array(
				"css" => "http://web.chiorichan.com/css",
				"js" => "http://web.chiorichan.com/js",
				"img" => "http://web.chiorichan.com/images"
				),
			"components" => array(
				"com.chiorichan.modules.users" => array(
					"scripts" => array(
						"login-form" => "http://web.chiorichan.com/login",
						"login-post" => "http://web.chiorichan.com/"
					),
					"table" => "users",
					"db-mode" => "sql"
					),
				"com.chiorichan.modules.db" => array(
					"host" => "",
					"port" => null,
					"database" => "",
					"username" => "",
					"password" => ""
					),
				"com.chiorichan.modules.template" => array(
					"title" => "Chiori-chan",
					"metatags" => array(),
					"protected" => array(), // folder paths you wish to protect from viewing. Can be either a file, folder or partial path. ie. /special-folder/secrets.html, /mystuff, /dir
					"source" => "/pages"
					)
				)
		);
		
		/* Array to keep track of loaded components */
		private $nameSpaceLoaded = array();
		
		/**
		 * Buffer array used to temporaraly store debug output.
		 * Once shutdown is called the buffer will be filtered
		 * and written to specified logs. If error occured then
		 * debug-level is ignored and all data is written.
		 */
		private $debug_buffer = array();
		
		function __construct($config)
		{
			$config = arrayMerge($this->config, $config);
			$this->config =& $config;
			
			/* Register ExceptionHandler function to be called on errors and exceptions if configuration allows. */
			if ($config["exception-handling"])
				set_error_handler(function($no,$str,$file,$line){__classCaller("ExceptionHandler", new ErrorException($str,$no,0,$file,$line)); }, error_reporting());
			if ($config["error-handling"])
				set_exception_handler(function($e) {__classCaller("ExceptionHandler", $e);});
			
			$this->Info("Product Name: " . $config["product"]);
			$this->Info("Version: " . $config["version"]);
			$this->Debug2("Copyright " . date("Y") . " Greenetree LLC");
			$this->Debug2("Author: Chiori Greene");
			$this->Debug2("Please do not redistribute without prior approval.");
			$this->Info("Framework Spawned as PID: " . getmypid());
			$this->Info("Log Level Set to " . $this->logLevelVar($config["debug"]["log"]) . "(" . $this->logLevelInt($config["debug"]["log"]) . ").");
			$this->Debug3("Framework Initalization Started...");
			$this->Debug3("Request: Client IP \"" . $_SERVER["REMOTE_ADDR"] . "\", Time \"" . $_SERVER["REQUEST_TIME"] . "\", URI \"" . $_SERVER["REQUEST_URI"] . "\" and Method \"" . $_SERVER["REQUEST_METHOD"] . "\".");
			
			$domain = explode(".", $_SERVER["SERVER_NAME"]);
			if (empty($this->config["domain"])) $this->config["domain"] = $domain[count($domain) - 1] . "." . $domain[count($domain)];
			
			/* Initilize User Space Strings */
			$this->nameSpaceBuild("strings");
			
			/* Initilize Database Component for Framework Use. Needs expansion to use Flat File Database by default. */
			$this->nameSpaceLoad("com.chiorichan.modules.db", array("host" => "", "database" => "", "username" => "", "password" => ""), "fw.db");
			
			/* Initilize extended functions component */
			$this->nameSpaceLoad("com.chiorichan.modules.functions");
			
			/* Initilize sessions component */
			$this->nameSpaceLoad("com.chiorichan.modules.sessions", array("domain" => $config["domain"]));
			
			/* Initilize settings component for internal use */
			$this->nameSpaceLoad("com.chiorichan.modules.settings", array("objDB" => "fw.db"), "fw.settings");
			
			/* Initilize Logging System */
			$config["debug"]["log-path"] = $this->patchPath($config["debug"]["log-path"]);
			if ( file_exists( $config["debug"]["log-path"] ) )
			{
				if ( !is_writable( $config["debug"]["log-path"] ) )
				{
					$this->Panic(500, "Logging system failed initilization... System failed to open log file for writting...");
				}
			}
			
			/* Yes, That is Japanese! Can you guess what it says? */
			$this->genkiDesuKa();
			
			/* Load additional components required by configuration */
			$this->Debug3("Initalizing components defined in configuration...");
			
			foreach ($config["components"] as $mod => $opts)
			{
				if (empty($mod) || !is_string($mod))
				{
					$mod = $opts;
					$opts = array();
				}
				
				$this->nameSpaceLoad($mod, $opts);
			}
		}
		
		function getAliases ()
		{
			return $this->config["aliases"];
		}
		
		/* Implementation of the import function similar to Java. */
		function import ($path)
		{
			if (empty($path))
				return false;
			
			$tmp = $this->nameSpaceGet($path);
			if ($tmp === false)
				return false;
			
			$foal = $this->nameSpaceChild($path);
			
			$GLOBALS[$foal] = $tmp;
			
			return $tmp;
		}
		
		function nameSpaceInclude($nameSpace) {if ($this->nameSpaceLoad($nameSpace, null, "", true)) echo $this->nameSpaceGet($nameSpace);}
		
		function nameSpaceLoad($nameSpace, $opts = array(), $nameSpaceObj = "", $downloadMissing = false, $overWriteExisting = false)
		{
			if (empty($nameSpaceObj))
			{
				$nameParent = $this->nameSpaceBuild($this->nameSpaceParent($nameSpace));
				$nameChild = $this->nameSpaceChild($nameSpace);
			}
			else
			{
				$nameParent = $this->nameSpaceBuild($this->nameSpaceParent($nameSpaceObj));
				$nameChild = $this->nameSpaceChild($nameSpaceObj);
			}
			
			$className = str_replace(".", "_", $nameSpace);
			
			$filename_inc = strtolower(__FW__ . "/" . str_replace(".", "/", $nameSpace) . ".inc.php");
			$filename_class = strtolower(__FW__ . "/" . str_replace(".", "/", $nameSpace) . ".class.php");
			
			$segtext = "Core: Loading component \"" . $nameSpace . "\"";
			$dl = false;
			
			if (!file_exists($filename_inc) && !file_exists($filename_class))
			{
				// Download Component
				if (!$this->downloadComponent($mod) && $downloadMissing)
				{
					$dl = true;
				}
				else
				{
					$this->InitDebug("Core: File \"" . $filename_inc . "\"", false, "FOUND", "NOT FOUND", 9);
					
					$this->InitDebug($segtext, true, "NOT FOUND");
					return false;
				}
			}
			elseif (file_exists($filename_class))
			{
				$filename = $filename_class;
				$class = true;
			}
			else
			{
				$filename = $filename_inc;
				$class = false;
			}
			
			if (!is_array($opts)) $opts = array();
			
			if (isset($nameParent->$nameChild) && !$overWriteExisting)
			{
				$this->InitDebug($segtext, true, "ALREADY LOADED");
				return false;
			}
			
			if ($class)
			{
				$this->InitDebug("Core: File \"" . $filename . "\"", true, "FOUND", "NOT FOUND", 9);
				
				if (!class_exists($className))
					include_once($filename);
				
				if (class_exists($className))
				{
					$nameParent->$nameChild = new $className($this, $opts);
					
					$this->nameSpaceLoaded[] = array("nameSpace" => $nameSpace, "parent" => $nameParent->fullNameSpace, "foal" => $nameChild, "class" => $className);
					
					$dl = ($dl) ? "DOWNLOADED" : "SUCCESS";
					$this->InitDebug($segtext, true, $dl);
				}
				else
				{
					$this->InitDebug($segtext, false, "", "FAILED");
				}
			}
			else
			{
				ob_start();
				@eval($this->globals());
				$chiori = $this;
				include($filename);
				$result = ob_get_contents();
				ob_end_clean();
				
				$nameParent->$nameChild = (string) $result;
				
				$this->nameSpaceLoaded[] = array("nameSpace" => $nameSpace, "parent" => $nameParent->fullNameSpace, "foal" => $nameChild, "class" => $className);
				$this->InitDebug($segtext, true, "SUCCESS");
			}
			
			return true;
		}
		
		function nameSpaceSet ($nameSpace, $value = "")
		{
			$nameParent = $this->nameSpaceBuild($this->nameSpaceParent($nameSpace));
			$nameChild = $this->nameSpaceChild($nameSpace);
			
			$nameParent->$nameChild = $value;
			$this->nameSpaceLoaded[] = array("nameSpace" => $nameSpace, "parent" => $nameParent->fullNameSpace, "foal" => $nameChild, "class" => $className);
			$this->InitDebug("Core: Manual Setting NameSpace \"" . $nameSpace . "\"", true, "SUCCESS");
		}
		
		function nameSpaceGet ($nameSpace)
		{
			$this->Debug1("Core: Getting NameSpace \"" . $nameSpace . "\"");
			
			$nameSpaceLocationDefault = "";
			$nameSpaceLocationAlt = "";
			
			foreach ($this->nameSpaceLoaded as $ns)
			{
				if ($ns["parent"] . "." . $ns["foal"] == $nameSpace)
				{
					$nameSpaceLocationDefault = $nameSpace;
				}
				
				if ($ns["nameSpace"] == $nameSpace)
				{
					$nameSpaceLocationAlt = $nameSpace;
				}
			}
			
			if (empty($nameSpaceLocationDefault) && empty($nameSpaceLocationAlt))
			{
				// nameSpace not loaded.
				return false;
			}
			elseif (empty($nameSpaceLocationDefault) && !empty($nameSpaceLocationAlt))
			{
				// Alternate nameSpace location found.
				$nameSpace = $nameSpaceLocationAlt;
			}
			
			$curNameSpace = $this;
			$nameSpace = explode(".", $nameSpace);
			foreach ($nameSpace as $obj)
			{
				if ($curNameSpace->$obj == null)
					return false;
				
				$curNameSpace = $curNameSpace->$obj;
			}
			
			return $curNameSpace;
		}
		
		function nameSpaceBuild ($nameSpace) // Returns pysical parent object.
		{
			$this->Debug1("Core: Building NameSpace \"" . $nameSpace . "\"");
			
			if (empty($nameSpace))
				return $this;
			
			$curNameSpace = $this;
			$nameSpace = explode(".", $nameSpace);
			$fullNameSpace = "";
			foreach ($nameSpace as $obj)
			{
				if (empty($fullNameSpace))
				{
					$fullNameSpace = $obj;
				}
				else
				{
					$fullNameSpace .= "." . $obj;
				}
				
				if ($curNameSpace->$obj == null)
					$curNameSpace->$obj = new classObject($fullNameSpace);
				
				$curNameSpace = $curNameSpace->$obj;
			}
			
			return $curNameSpace;
		}
		
		function nameSpaceParent($nameSpace) // Return string name of parent
		{
			return substr($nameSpace, 0, strrpos($nameSpace, "."));
		}
		
		function nameSpaceChild($nameSpace) // Return string name of foal.
		{
			$nameSpace = explode(".", $nameSpace);
			return $nameSpace[count($nameSpace) - 1];
		}
		
		function isSite($site) {return (strpos($site, ".") !== false);}
		
		function Version() { return $this->config["version"]; }
		
		// Note: Add check to prevent overwriting of protected configuration vars.
		function confMerge ($conf) { if (is_array($conf)) $this->config = arrayMerge($this->config, $conf); }
		
		function exeCode($source)
		{
			if (empty($source))
				return false;
			
			foreach ($this->config["aliases"] as $key => $val)
			{
				$keys[] = "%" . $key . "%";
				$vals[] = $val;				
			}
		
			$source = str_replace($keys, $vals, $source);
			
			unset($keys);
			$keys = "";
			
			$disallowed = array("_", "__qca", "__cs_rr", "fc", "__utma", "__utmb", "__utmc", "__utmz", "argv", "argc", "erl");
			foreach ($GLOBALS as $key => $val)
			{
				if (strtolower($key) == $key && !in_array($key, $disallowed)) $keys .= ", \$" . $key;
			}
	
			$new_source = "global " . substr($keys, 2) . "; ?>" . $source . "<? ";			
			
			$return = eval($new_source);
			
			if ( $return === false && ( $error = error_get_last() ) )
			{
				$this->Warning(500, $error["message"] . " on line " . $error["line"]);
				exit;
			}
		}
		
		// Needs updating
		function componentSite($com = "")
		{
			if (empty($com))
			{
				foreach (array_reverse(explode(".", substr($_SERVER["SERVER_NAME"], strrpos($_SERVER["SERVER_NAME"], ".", -6) + 1))) as $el)
				{
					if (empty($root))
					{
						$root = $el;
					}
					else
					{
						$root .= "." . $el;
					}
				}
			}
			else
			{
				$com = explode(".", $com);
				$root = $com[0] . "." . $com[1];
			}
			
			return $root;
		}
		
		function genkiDesuKa()
		{
			if (!$this->InitDebug("Checking if Framework Root is Writable", is_writable(__FW__), "YES", "NO", 9)) $this->Panic(500, "\"" . __FW__ . "\" is not writable by Framework. Check Permissions");
			
			if (file_exists(__FW__ . "/firstrun"))
			{
				//$this->Info("First run flag detected...");
			}
		}
		
		function patchPath ($path)
		{
			if (empty($path)) return;
			
			if (substr($path, 0, 1) != "/")
			{
				$path = __FW__  . "/" . $path;
			}
			
			return $path;
		}
		
		// Needs updating
		function isComponent($com)
		{
			$loaded = false;
			foreach ($this->loaded_components as $ech)
			{
				if ($ech == $com) $loaded = true;
			}
			return $loaded;
		}
		
		function globals ()
		{
			$disallowed = array("GLOBALS", "_POST", "_GET", "_SERVER", "_FILES", "_COOKIE");
			$vars = array();
			foreach($GLOBALS as $k => $v)
			{
				if (!in_array($k, $disallowed))
					$vars[] = "$".$k;
			}
			return "global ".  join(",", $vars).";";
		}
		
		function includeComponent($com, $return = false, $downloadMissing = true)
		{
			if (empty($com)) return false;
			
			$filename = strtolower(__FW__ . "/" . str_replace(".", "/", $com) . ".inc.php");
			$this->Debug2("Core: Loading component \"" . $com . "\" from \"" . $filename . "\"");
			$segtext = "Core: Loading component \"" . $com . "\" from file";
			$ok = "SUCCESS";
			
			if ($downloadMissing)
			{
				if (!file_exists($filename))
				{
					// Download Component
					if ($this->DownloadComponent($com))
					{
						$ok = "DOWNLOADED";
					}
					else
					{
						$this->InitDebug($segtext, false, "NOT FOUND");
						return false;
					}
				}
			}
			else
			{
				$this->InitDebug($segtext, false, "", "NOT FOUND");
				return false;
			}
			
			//$this->loaded_components[] = $com;
			
			@eval($this->globals()); // Load all global strings into userspace.
			$chiori = $this; // Create the Parent fw string
			
			foreach ($this->config["aliases"] as $key => $val)
			{
				$keys[] = "%" . $key . "%";
				$vals[] = $val;				
			}
			
			ob_start(); // Start Output Buffer Session.
			include($filename); // Include requested file to to be captured by ob.
			$result = ob_get_contents(); // Retreive output buffer contents.
			ob_end_clean(); // Erase ob contents.
			
			$result = str_replace($keys, $vals, $result);
			
			$this->InitDebug($segtext, true);
			
			if ($return)
			{
				
				return $result; // Return output to requesting subroutine.
			}
			else
			{
				echo($result);
			}
		}
		
		// Needs patching for new namespace system
		function downloadComponent($com)
		{
			if (empty($com)) return false;
			
			$this->Info("Attempting to download missing component: " . $com);
			
			$path = explode(".", $com);
			$filename = strtolower(__FW__ . "/" . str_replace(".", "/", $com) . ".inc.php");
			
			$domain = $path[0];
			$l = 1;
			
			while (true)
			{
				$result = (@file_get_contents("http://" . $domain . "/index.php?request=ping") == "HELLO WORLD");
				$this->InitDebug("Polling \"" . $domain . "\" for existence of Chiori Framework", $result);
				if ($result) break;

				$domain = $path[$l] . "." . $domain;
				$l++;
				
				if ($l > count($path) - 1) break;
			}
			
			if (!$result)
			{
				$this->Info("Download of missing component failed... Could not find a suitable repository... See wiki for help.");
				return false;
			}
			
			$this->Debug1("Sending download request: \"http://" . $domain . "/index.php?request=dlcom&seckey=" . md5(date("YmD") . $com) . "&opts=" . $com . "\"");
			if ($contents = @file_get_contents("http://" . $domain . "/index.php?request=dlcom&seckey=" . md5(date("YmD") . $com) . "&opts=" . $com))
			{
				if (!file_exists(dirname($filename))) mkdir(dirname($filename), 0777, true);
				$handle = fopen($filename, "w");
				
				if (fwrite($handle, $contents))
				{
					$this->Info("Download of missing component was successful...");
				}
				else
				{
					$this->Info("Download of missing component failed... Unable to write stream to file... See wiki for help.");
				}
				
				fclose($handle);
			}
			else
			{
				$this->Info("Download of missing component failed... Repository returned failure... See wiki for help.");
				return false;
			}
		}
		
		function Panic($errno, $msg, $line_no = null, $file = null, $stackTrace = null)
		{
			$template = $this->nameSpaceGet("com.chiorichan.modules.template");
			if ($tempate === false)
			{
				echo("<h1>" . $errno . " - " . $msg . "</h1>");
				if (!is_null($line_no))
					echo "<p>Line #: " . $line_no . "</p>";
				if (!is_null($file))
					echo "<p>File: " . $file . "</p>";
				die();
			}
			else
			{
				$template->loadErrorPage($errno, $msg, $line_no, $file, $stackTrace);
			}
		}
		
		function InitDebug($msg, $bool, $ok = "SUCCESS", $fail = "FAILED", $level = 6)
		{
			switch ($bool)
			{
				case true:
					$result = "[" . $ok . "]";
					break;
					
				case false:
					$result = "[" . $fail . "]";
					break;
				
				default:
					$result = "[" . $fail . "]";
					break;
			}
			
			$len = 120 - strlen($msg) - strlen($result);
			if ($len < 0) $len = 0;
			
			$this->Logger($level, $msg . str_repeat(".", $len) . $result);
			
			return $bool;
		}
		
		/*
		 * Disable logging for session.
		 * Some website processes such as API or status-checkups
		 * may want to be skiped to prevent overpopulating logs.
		 */
		public function noLogging ()
		{
			$this->config["debug"]["log"] = -1;
		}
		
		function Debug1($msg){$this->Logger(9, $msg);}
		function Debug2($msg){$this->Logger(8, $msg);}
		function Debug3($msg){$this->Logger(7, $msg);}
		function Info($msg){$this->Logger(6, $msg);}
		function Notice($msg){$this->Logger(5, $msg);}
		function Warning($msg){$this->Logger(4, $msg);}
		function Error($msg){$this->Logger(3, $msg);}
		function Critical($msg){$this->Logger(2, $msg);}
		function Alert($msg){$this->Logger(1, $msg);}
		function Emergency($msg){$this->Logger(0, $msg);}
		function logit($level, $msg){$this->Logger($level, $msg);}
		
		private function Logger($level, $msg)
		{
			$bt = debug_backtrace();
			$bt = $bt[1];
			
			$this->debug_buffer[] = array(date("h:i:") . $this->getseconds(), basename($bt["file"]), $bt["line"], $level, $msg);
			
			//syslog($level, $this->getseconds() . " " . $bt["file"] . " " . $bt["line"] . " " . $this->logLevelVar($level) . ": " . $msg);
		}
		
		function getseconds()
		{
			$mill_sec = round(microtime(true) - date("U"), 4);
			return (date("s") + $mill_sec) . str_repeat("0", 6 - strlen($mill_sec));
		}
		
		function logLevelInt($level)
		{
			switch (strtoupper($level))
			{
				case "LOG_DISABLED": $debug_level = -1; break;
				case "LOG_EMERG": $level = 0; break;
				case "LOG_ALERT": $level = 1; break;
				case "LOG_CRIT": $level = 2; break;
				case "LOG_ERR": $level = 3; break;
				case "LOG_WARNING": $level = 4; break;
				case "LOG_NOTICE": $level = 5; break;
				case "LOG_INFO": $level = 6; break;
				case "LOG_DEBUG3": $level = 7; break;
				case "LOG_DEBUG2": $level = 8; break;
				case "LOG_DEBUG1": $level = 9; break;
			}
			
			return $level;
		}
		
		function logLevelVar($level)
		{
			switch ($level)
			{
				case "-1": $level = "Disabled"; break; // Logs Disabled
				case "0": $level = "Emergency"; break; // System Unusable
				case "1": $level = "Alert    "; break; // Action must be taken immediately
				case "2": $level = "Critical "; break;
				case "3": $level = "Error    "; break;
				case "4": $level = "Warning  "; break;
				case "5": $level = "Notice   "; break; // Normal, but significant, condition
				case "6": $level = "Info     "; break; // Informational message
				case "7": $level = "Debug 3  "; break; // Debug Level 3 Message
				case "8": $level = "Debug 2  "; break; // Debug Level 2 Message
				case "9": $level = "Debug 1  "; break; // Debug Level 1 Message
			}
			
			return $level;
		}
		
		function ExceptionHandler ($e) // Needs additional code added in future.
		{
			$e = error_get_last();
			if ($e != null && $e["type"] < 8)
			{
				switch ($e["type"])
				{
					case "1": $level = "Fatal"; break;
					case "2": $level = "Warning"; break;
					case "4": $level = "Parse"; break;
					case "8": $level = "Notice"; break;
				}
				
				$this->logit($e["type"], $e["message"] . " in " . $e["file"] . " on line " . $e["line"]);
				$e = new ErrorException($level . " error: " . $e["message"], 0, $e["type"], $e["file"], $e["line"]);
				
				if (isset($this->functions))
				{
					// $this->functions->errorPanic($e);
				}
			}
		}
		
		/**
		 * Performs some basic checks after the execution of PHP Framework.
		 * Takes buffered debug log and appends it to the mane Chiori Log.
		 */
		function ShutdownHandler ()
		{
			$e = error_get_last();
			if ($e != null && $e["type"] < 8)
			{
				switch ($e["type"])
				{
					case "1": $level = "Fatal"; break;
					case "2": $level = "Warning"; break;
					case "4": $level = "Parse"; break;
					case "8": $level = "Notice"; break;
				}
				
				$this->logit($e["type"], $e["message"] . " in " . $e["file"] . " on line " . $e["line"]);
				$e = new ErrorException($level . " error: " . $e["message"], 0, $e["type"], $e["file"], $e["line"]);
				
				if (isset($this->functions))
				{
					// $this->functions->errorPanic($e);
				}
			}
			
			$length = 150;
			
			/* Perform log configuration checks */
			
			/* Combine Logs */
			if ($handle = fopen($this->config["debug"]["log-path"], "a"))
			{
				$debug_level = $this->config["debug"]["log"];
				
				if ($this->nameSpaceGet("com.chiorichan.modules.settings")->get("USER_DEBUG_LEVEL") != "LOG_DISABLED")
					$debug_level = $this->logLevelInt($this->nameSpaceGet("com.chiorichan.modules.settings")->get("USER_DEBUG_LEVEL"));
				
				$log = "";
				foreach ($this->debug_buffer as $msg)
				{
					if ($this->logLevelInt($msg[3]) <= $debug_level)
					{
						$message = $msg[4];
						$op = array();
						
						
						do
						{
							if (strlen($message) > $length)
							{
								$op[] = substr($message, 0, $length);
								$message = substr($message, $length);
							}
							else
							{
								$op[] = $message;
								$message = "";
							}
						}
						while (!empty($message));
						
						$log .= $op[0] . str_repeat(" ", $length - strlen($msg[4])) . " --> " . $msg[0] . " " . $this->logLevelVar($msg[3]) . " " . $msg[2] . str_repeat(" ", 6 - strlen($msg[2])) . " " . $msg[1] . "\n";
						
						if (count($op) > 1)
						{
							for ($x=1;$x<=count($op);$x++)
							{
								if (!empty($op[$x]))
									$log .= $op[$x] . "\n";
							}
						}
					}
				}
				
				if (!empty($log))
				{
					$logOP = "\n\n<--- Mark --->\n";
					$logOP .= "<Log Message>" . str_repeat(" ", $length - 13) . "     <Time>        <Level>   <Line> <File>\n";
					$logOP .= $log;
					$logOP .= "Script Execution: " . (microtime(true) - CHI_BEGIN_TIME) . "\n";
					$logOP .= "Process ID: " . getmypid() . "\n";
					$logOP .= "Date: " . date("M d Y") . "\n";
					fwrite($handle, $logOP);
				}
				
				fclose($handle);
			}
		}
	}
	
	/* Initalize Framework */
	$chiori = new ChioriFWBase($conf);
	$GLOBALS["chiori"] =& $chiori;
	
	/* First seeds of Framework Based Panel - A work in progress */
	/* Includes checks if client is another Chiori Framework installation */
	switch ($_REQUEST["request"])
	{
		/* If we receive a "status" request then reply with "STATUS=OK". Expansion needed. */
		case "status":
			$this->Info("Framework Interupted. Remote Client was making a poll for the status of this framework. Replying with status and terminating...");
			die("STATUS=OK");
			break;
			
		/* If we receive a "ping" request then reply with "HELLO WORLD". Possibly will be replaced by the status routine instead. */
		case "ping":
			$this->Info("Framework Interupted. Remote Client was making a poll for the existance of this framework. Replying to ping and terminating...");
			die("HELLO WORLD");
			break;
			
		/*
		 * Currently other Chiori Frameworks download new components by making a "dlcom" request
		 * There are plans to replace with with repositories and improve the existing with better security and access-control policies.
		 */
		case "dlcom":
			if ($_REQUEST["seckey"] == md5(date("YmD") . $_REQUEST["opts"]))
			{
				$filename = __FW__ . "/" . strtolower(str_replace(".", "/", $_REQUEST["opts"]) . ".inc.php");
				
				if (file_exists($filename))
				{
					die(file_get_contents($filename));
				}
				else
				{
					header("Status: 404 Not Found");
					die();
				}
			}
			header("Status: 404 Not Found");
			die();
			break;
	}
	
	/*
	 * Check if the URL being called from the browser is the same as the framework URL.
	 * Deny access for this would mean no loader or site page was ever called.
	 * We also reccommend to secure this further by denying access to the "protected" folder using .htaccess
	 */
	if (realpath($_SERVER["SCRIPT_FILENAME"]) == __FILE__)
	{
		$chiori->Panic(403, "You are not authorized to view internal server files.");
	}
	
	/* Write in logs that Framework Initalization was a success. */
	$chiori->InitDebug("Core: Framework Initalization", true);
	
	
	
	// Disable Browser Caching. Internet Explorer is the worst at caching web documents.
	// Needs inprovment to provide what caching really does when needed; speeding up browsing.
	//header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	//header("Cache-Control: no-store, no-cache, must-revalidate");
	//header("Cache-Control: post-check=0, pre-check=0", false);
	//header("Pragma: no-cache");
?>
