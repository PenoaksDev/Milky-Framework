<?php
	class Server
	{
		protected $allowIPs = array();
		protected $denyIPs = array();
		protected $whitelist = false;
		protected $serverName = "Unnamed Chiori Framework Server";
		
		private $default_session_lifetime = 604800; // 43200 = 12 hours
		private $default_session_name = "AppleBloom";
		private $data = array();
		private $domain;
		
		protected $firstCall = true;
		
		function __construct()
		{
			// TODO: Temp
			$this->serverName = "Apple Bloom Framework Server #1";
		}
		
		public function banIP($addr)
		{
			$this->denyIPs[] = $ipaddr;
		}
		
		public function Panic ( $status, $desc = null )
		{
			if ( !headers_sent() )
				header("Status: " . $status . " " . $this->statusMessage( $status ));
			
			if ( $desc == null )
				$desc = $this->statusMessage( $status );
			
			echo $status . ": " . $desc;
			exit;
		}
		
		public function unbanIP($addr)
		{
			foreach ( $this->denyIPs as $key => $val )
			{
				if ( strtolower($val) == strtoupper($addr) )
					unset ( $this->denyIPs[$key] );
			}
		}
		
		public function setWhitelist($bool)
		{
			if ( typeof($bool) != "Boolean" )
				return false;
			
			$this->whitelist = $bool;
		}
		
		public function getServerName()
		{
			return $this->serverName;
		}
		
		public function getPackage ( $package )
		{
			return $this->includePackage( $package, true );
		}
		
		public function includePackage ( $package, $return = false )
		{
			if ( $package == null || empty( $package ) )
			{
				if ( $return )
					return "";
				
				return false;
			}
			
			if ( strpos($package, ".") === false )
				$package = "com.chiorichan.plugin." . $package;
			
			$path = FW . DIRSEP . str_replace(".", DIRSEP, $package) . ".php";
			
			if ( !file_exists( $path ) )
			{
				$path = FW . DIRSEP . str_replace(".", DIRSEP, $package) . ".inc.php";
				
				if ( !file_exists( $path ) )
				{
					if ( $return )
						return "";
						
					return false;
				}
			}
			
			$this->Debug3("Retrieving File: " . $path);
			
			//$source = file_get_contents( $path );
			
			$source = $this->fileReader( $path );
			
			if ( $return )
				return $source;
			
			echo $source;
			return true;
		}
		
		/**
		 * Read and execute php file and return in a string.
		 */
		public function fileReader ( $filename )
		{
			if ( $filename == null || empty( $filename ) )
				return;
				
			if (!file_exists($filename))
				return;
			
			$chiori = getFramework();
			
			$keys = array();
			$vals = array();
			
			foreach ($chiori->getConfigurationManager()->getArray("aliases") as $key => $val)
			{
				$keys[] = "%" . $key . "%";
				$vals[] = $val;
			}
			
			foreach ($chiori->getConfigurationManager()->getArray("aliases", CONFIG_FW) as $key => $val)
			{
				$keys[] = "%" . $key . "%";
				$vals[] = $val;
			}
			
			ob_start(); // Start Output Buffer Session.
			include($filename); // Include requested file to to be captured by ob.
			$result = ob_get_contents(); // Retreive output buffer contents.
			ob_end_clean(); // Erase ob contents.
			
			$result = str_replace($keys, $vals, $result);
			
			return $result; // Return output to requesting subroutine.
		}
		
		public function runSource( $source )
		{
			if ( empty( $source ) )
				return false;
			
			$keys = array();
			$vals = array();
			
			foreach (getFramework()->getConfig()->getArray("aliases", CONFIG_SITE) as $key => $val)
			{
				$keys[] = "%" . $key . "%";
				$vals[] = $val;
			}
			
			$source = str_replace($keys, $vals, $source);
			
			/*
			$disallowed = array("_", "__qca", "__cs_rr", "fc", "__utma", "__utmb", "__utmc", "__utmz", "argv", "argc", "erl");
			foreach ($GLOBALS as $key => $val)
			{
				if (strtolower($key) == $key && !in_array($key, $disallowed)) $keys .= ", \$" . $key;
			}
			
			$new_source = "global " . substr($keys, 2) . "; ?>" . $source . "<? ";
			
			$disallowed = array("GLOBALS", "_POST", "_GET", "_SERVER", "_FILES", "_COOKIE");
			$vars = array();
			foreach($GLOBALS as $k => $v)
			{
				if (!in_array($k, $disallowed))
					$vars[] = "$".$k;
			}
			return "global ".  join(",", $vars).";";
			*/
			
			$chiori = getFramework();
			
			try
			{
				$return = eval("?>" . $source . "<?php ");
			}
			catch ( Exception $e )
			{
				throw $e;
			}
				
			if ( $return === false && ( $e = error_get_last() ) )
			{
				throw new RuntimeException($e["message"] . " on line " . $e["line"], 500);
				exit;
			}
		}
		
		// Logging System
		
		public function sendException( $msg, $level = LOG_ERR )
		{
			$this->rawData( $msg, $level );
		}
		
		// TODO: Initate connection with daemon process and send information.
		
		function Debug($msg){$this->rawData($msg, LOG_DEBUG);}
		function Debug1($msg){$this->rawData($msg, LOG_DEBUG1);}
		function Debug2($msg){$this->rawData($msg, LOG_DEBUG2);}
		function Debug3($msg){$this->rawData($msg, LOG_DEBUG3);}
		function Info($msg){$this->rawData($msg, LOG_INFO);}
		function Notice($msg){$this->rawData($msg, LOG_NOTICE);}
		function Warning($msg){$this->rawData($msg, LOG_WARNING);}
		function Error($msg){$this->rawData($msg, LOG_ERR);}
		function Critical($msg){$this->rawData($msg, LOG_CRIT);}
		function Alert($msg){$this->rawData($msg, LOG_ALERT);}
		function Emergency($msg){$this->rawData($msg, LOG_EMERG);}
		
		private function getSeconds()
		{
			$mill_sec = round(microtime(true) - date("U"), 4);
			return (date("s") + $mill_sec) . str_repeat("0", 6 - strlen($mill_sec));
		}
		
		public function getLogLevelName ( $level )
		{
			switch ( $level )
			{
				case LOG_DISABLED:	$level = "Disabled "; break;
				case LOG_DEBUG3:	$level = "Debug 3  "; break;
				case LOG_DEBUG2:	$level = "Debug 2  "; break;
				case LOG_DEBUG1:	$level = "Debug 1  "; break;
				case LOG_DEBUG:		$level = "Debug    "; break;
				case LOG_INFO:		$level = "Info     "; break;
				case LOG_NOTICE:	$level = "Notice   "; break;
				case LOG_WARNING:	$level = "Warning  "; break;
				case LOG_ERR:		$level = "Error    "; break;
				case LOG_CRIT:		$level = "Critical "; break;
				case LOG_ALERT:		$level = "Alert    "; break;
				default:			$level = "Unknown  "; break;
			}
				
			return $level;
		}
		
		public function rawData ($message, $level = LOG_DEBUG)
		{
			if ( $this->firstCall )
				@file_put_contents("/var/log/chiori.log", join(", ", $_REQUEST) . "\n", FILE_APPEND);
			
			$this->firstCall = false;
			
			if ( $_SERVER["REMOTE_ADDR"] != "50.79.49.249" )
				return;
			
			// TODO: Add a Better Logger System.
			
			$log = "";
			$length = 120;
				
			if ( $this->firstCall )
				$log .= "\n\n<Log Message>" . str_repeat(" ", $length - 13) . "     <Time>        <Level>   <Line> <File>\n";
				
			$this->firstCall = false;
				
			$op = array();
				
			do
			{
				if ( strlen($message) > $length )
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
				
			$bt = debug_backtrace();
			$bt = $bt[1];
			$arr = array(date("h:i:") . $this->getseconds(), basename($bt["file"]), $bt["line"], $level);
				
			$str = Colors::translateAlternateColors($op[0]) . Colors::RESET;
			$log .= $str . str_repeat(" ", $length - strlen($op[0])) . " --> " . $arr[0] . " " . $this->getLogLevelName($arr[3]) . " " . $arr[2] . str_repeat(" ", 6 - strlen($arr[2])) . " " . $arr[1] . "\n";
				
			if (count($op) > 1)
			{
				for ($x=1;$x<=count($op);$x++)
				{
					if (!empty($op[$x]))
						$log .= Colors::translateAlternateColors($op[$x]) . Colors::RESET . "\n";
				}
			}
			
			if ($handle = fopen(__ROOT__ . "/log/chiori.log", "a"))
			{
					fwrite($handle, $log);
					fclose($handle);
			}
		}
		
		public function sendRedirect ( $StrURL, $RedirectCode = 301, $AutoRedirect = true )
		{
			header("HTTP/1.1 " . $RedirectCode . ' ' . $this->statusMessage($RedirectCode));
			if ($AutoRedirect)
			{
				if (headers_sent())
				{
					$this->dummyRedirect($StrURL);
				}
				else
				{
					header("Location: " . $StrURL);
				}
			}
			else
			{
				// TODO: Needs attention!!!
				//			$this->SpecialPage($RedirectCode, "The Request URL has been relocated to: " . $StrURL . "<br />Please change any bookmarks to reference this new location.");
			}
		}
		
		public function setStatus ( $code )
		{
			if ( !headers_sent() )
				header("HTTP/1.1 " . $code . ' ' . $this->statusMessage($code));
		}
		
		public function statusMessage($status)
		{
			$codes = Array(
					100 => 'Continue',
					101 => 'Switching Protocols',
					200 => 'OK',
					201 => 'Created',
					202 => 'Accepted',
					203 => 'Non-Authoritative Information',
					204 => 'No Content',
					205 => 'Reset Content',
					206 => 'Partial Content',
					300 => 'Multiple Choices',
					301 => 'Moved Permanently',
					302 => 'Found',
					303 => 'See Other',
					304 => 'Not Modified',
					305 => 'Use Proxy',
					306 => '(Unused)',
					307 => 'Temporary Redirect',
					400 => 'Bad Request',
					401 => 'Unauthorized',
					402 => 'Payment Required',
					403 => 'Forbidden',
					404 => 'Not Found',
					405 => 'Method Not Allowed',
					406 => 'Not Acceptable',
					407 => 'Proxy Authentication Required',
					408 => 'Request Timeout',
					409 => 'Conflict',
					410 => 'Gone',
					411 => 'Length Required',
					412 => 'Precondition Failed',
					413 => 'Request Entity Too Large',
					414 => 'Request-URI Too Long',
					415 => 'Unsupported Media Type',
					416 => 'Requested Range Not Satisfiable',
					417 => 'Expectation Failed',
					500 => 'Internal Server Error',
					501 => 'Not Implemented',
					502 => 'Bad Gateway',
					503 => 'Service Unavailable',
					504 => 'Gateway Timeout',
					505 => 'HTTP Version Not Supported'
			);
		
			return (isset($codes[$status])) ? $codes[$status] : '';
		}
		
		public function dummyRedirect($url)
		{
			echo("<script>window.location = '" . $url . "';</script>");
		}
		
		// Sessions Section
		// TODO: Work Needed
		
		public function initSession ()
		{
			session_destroy();
			session_name( $this->default_session_name );
			session_set_cookie_params( time() + $this->default_session_lifetime, "/", "." . getFramework()->domainName );
			session_start();
			
			if ( isset( $_COOKIE[ $this->default_session_name ] ) )
				setcookie( $this->default_session_name, $_COOKIE[ $this->default_session_name ], time() + $this->default_session_lifetime, "/", "." . getFramework()->domainName );
		}
		
		public function getSessionString ( $key, $default = null )
		{
			if ( empty( $_SESSION[$key] ) && $default != null )
			{
				return $default;
			}
			else
			{
				return $_SESSION[$key];
			}
		}
		
		public function setSessionString ( $key, $value = "" )
		{
			$_SESSION[$key] = $value;
			
			//$this->Info("[ChioriSessions] Updated sessions varable \"" . $key . "\" to \"" . $value . "\"");
			//$this->data = arrayJoin($this->data, array($key => $value));
			//$this->__SetSession($this->data["SessID"]);
				
			return true;
		}
		
		public function setCookieExpiry ($valid)
		{
			session_set_cookie_params( time() + $valid, "/", "." . getFramework()->domainName );
		}
		
		public function destroySession ($SessID = "")
		{
			session_destroy();
		}
	}
