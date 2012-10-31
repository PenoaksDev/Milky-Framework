<?
	/**
	 * (C) 2012 Chiori Greene
	 * All Rights Reserved.
	 * Author: Chiori Greene
	 * E-Mail: chiorigreene@gmail.com
	 * 
	 * This class is intellectual property of Chiori Greene and can only be distributed in whole with its parent
	 * framework which is known as Chiori Framework. If you wish to alter and redistribute any part of it we
	 * require you make the changes by extending the class within the extension file.
	 * 
	 * Keep software like this free and open source by following the authors wishes.
	 * 
	 * Class Name: Chiori Template
	 * Version: 1.0.0 Offical Release
	 * Released: March 20th, 2012
	 * Description: This class is used to reproduce a common template accross several pages with the least
	 * number of edits.
	 */

	class com_chiorichan_modules_template extends ModuleTemplate
	{
		private $titleOverWrite = "";
		private $headExtras = array();
		
		function loadErrorPage ($errno, $msg, $line_no = null, $file = null, $stackTrace = null)
		{
			if (empty($msg))
				$msg = $this->statusMessage($errno);
			
			switch ($errno)
			{
				case "404":
					$page = "notfound";
					break;
				default:
					$page = "panic";
					break;
			}
			
			if (is_null($line_no))
				$line_no = __LINE__;
			if (is_null($file))
				$file = __FILE__;
			
			if (is_null($stackTrace))
				$GLOBALS["stackTrace"] = $stackTrace;
			
			$GLOBALS["lasterr"] = new ErrorException($msg, 0, $errno, $file, $line_no);
			
			$html = $this->chiori->nameSpaceInclude("com.chiorichan.pages." . $page);
			
			$this->TemplateLoad($html, "Chiori Framework Encountered an Error", "com.chiorichan.themes.error");
			
			die();
		}
		
		function headAdd ($headString)
		{
			foreach ($this->chiori->getAliases() as $key => $val)
			{
				$headString = str_replace("%" . $key . "%", $val, $headString);
			}
			
			$this->headExtras[] = $headString . "\n";
		}
		
		function loadPanicPage ($errno, $msg)
		{
			if (empty($msg))
				$msg = $errno . " - " . $this->statusMessage($errno);
			
			$GLOBALS["lasterr"] = new ErrorException($msg, 0, $errno, __FILE__, __LINE__);
			$html = $this->chiori->nameSpaceInclude("com.chiorichan.pages.panic");
			
			$this->TemplateLoad($html, "Chiori Framework had a Panic", "com.chiorichan.themes.error");
			
			die();
		}
		
		function loadPage ($page = "", $domain = "", $errorIfNotFound = true)
		{
			$rows = array();
			$segtext = "ChioriTemplate: Searching for \"" . $page . "\" within Database";
			
			if (empty($domain)) $domain = $_SERVER["SERVER_NAME"];
			
			$site = "";
			if (substr_count($domain, ".") > 1)
			{
				$site = substr($domain, 0, strrpos($domain, ".", -6));
				$domain = substr($domain, strrpos($domain, ".", -6) + 1);
			}
			
			$r1 = $this->chiori->fw->db->select("pages", array(array("site" => "", "|site" => $site), "domain" => $domain));
			$r2 = $this->chiori->fw->db->select("pages", array(array("site" => "", "|site" => $site), "domain" => ""));
			
			$result = array_merge($r1, $r2);
			
			if ($result !== false)
			{
				foreach($result as $row)
				{
					$arr = $row["page"];
			
					if (substr($page, 0, 1) == "/") $page = substr($page, 1);
					if (substr($arr, 0, 1) == "/") $arr = substr($arr, 1);
		
					$src = explode("/", $page);
					$desc = explode("/", $arr);
		
					$whole_match = true;
					for ($i = 0; $i <= count($desc); $i++)
					{
		
						$this->chiori->Debug1("Step " . $i . " - " . $arr . ": " . $src[$i] . " ==> " . $desc[$i] . " ...");
				
						if (preg_match("/\[([a-zA-Z0-9]+)=\]/", $desc[$i], $match))
						{
							$exp = "[" . $match[1] . "=]";
							$lrtn = substr($desc[$i], 0, strpos($desc[$i], $exp));
							if (!$rrtn = substr($desc[$i], strlen($exp) - strpos($desc[$i], $exp))) $rrtn = "";
	
							if ((empty($lrtn) || substr($src[$i], 0, strlen($lrtn)) == $lrtn) && (empty($rrtn) || substr($src[$i], 0 - strlen($rrtn)) == $rrtn))
							{
								$op = str_replace($lrtn, "", $src[$i]);
								$op = str_replace($rrtn, "", $op);
					
								$GLOBALS[$match[1]] = $op;
								$_GET[$match[1]] = $op;
								$_POST[$match[1]] = $op;
								$_REQUEST[$match[1]] = $op;
								$this->chiori->Debug1("MATCH-PREG");
								$row["preg"] = true;
							}
							else
							{
								$this->chiori->Debug1("NO-MATCH");
								$whole_match = false;
								break;
							}
						}
						elseif ($src[$i] == $desc[$i])
						{
							$this->chiori->Debug1("MATCH");
						}
						else
						{
							$this->chiori->Debug1("NO-MATCH");
							$whole_match = false;
							break;
						}
					}
					
					if ($whole_match) $rows[] = $row;
				}
			}

			if (count($rows) > 1)
			{
				for ($i = 0; $i <= count($rows); $i++)
				{
					if ($rows[$i]["preg"] && count($rows) > 1) unset($rows[$i]);
				}
			}

			if (count($rows) > 0)
			{
				$rows = array_merge($rows);
				
				$this->chiori->InitDebug($segtext, true, $rows[0]["page"]);
				
				$this->VirtualPage($rows[0]);
				exit;
			}
			
			$this->chiori->InitDebug($segtext, false);
			
			return $this->findLocally ($page, $errorIfNotFound);
		}
		
		function findLocally ($page, $errorIfNotFound = true)
		{
			/* Attemt to find the requested file locally. */
			$segtext = "Template: Searching for \"" . $page . "\" as locally stored file";
			$filename = $_SERVER["DOCUMENT_ROOT"] . "/" . $page;
			if (file_exists($filename) && !is_dir($filename))
			{
				chdir ( dirname ( $filename ) );
				
				//$this->chiori->confMerge(array("debug" => array("log" => -1))); // Disable log on locally loaded files.
				
				if (substr($filename, -3) == "php")
				{
					include($filename);
				}
				else
				{
					if (false) //filesize($filename) > 268435456)
					{
						// File too large to load
					}
					else
					{
						switch (substr($filename, -3))
						{
							case "jpg":
								header("Content-type: image/jpeg");
								break;
							case "png":
								header("Content-type: image/png");
								break;
							case "css":
								header("Content-type: text/css; charset=utf-8");
								break;
							case ".js":
								header("Content-type: text/javascript; charset=utf-8");
								break;
							case "gif":
								header("Content-type: image/gif");
								break;
							case "pdf":
								header("Content-type: application/pdf");
								break;
							case "xml":
								header("Content-type: application/xml");
								break;
							case "swf":
								header("Content-type: application/x-shockwave-flash");
								break;
						}
						
			//			header("Cache-Control: no-cache, must-revalidate");
			//			header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
						
						$this->chiori->Debug2("Outputing from local file \"" . $filename . "\" with mime type \"" . $ftype . "\"");
						
						$handle = fopen($filename, "r");
						// echo fread($handle, filesize($_SERVER["DOCUMENT_ROOT"] . "/" . $page));
						
						// Fixed memory fill error by reading only 4096 byte at a time
						$readsize = 4096;
						while (!feof($handle))
						{
							echo fread($handle, $readsize);
						}
						
						fclose($handle);
					}
				}
				
				$this->chiori->InitDebug($segtext, true, "FOUND");
				die();
			}
			elseif (is_dir($filename))
			{
				// Display Directory
				$this->chiori->InitDebug($segtext, true, "Folder, Not Fully Implemented");
				
				if ( substr( $page, -1 ) != "/" )
				{
					$this->chiori->nameSpaceGet("com.chiorichan.modules.functions")->redirectURL("/" . $page . "/");
					die ();
				}
				
				return $this->findLocally ($page . "/index.php", $errorIfNotFound);
			}
			else
			{
				// Not Found
				$this->chiori->InitDebug($segtext, false);
				
				switch (substr($filename, -3))
				{
					case "jpg":
						header("Content-type: image/jpeg");
						break;
					case "png":
						header("Content-type: image/png");
						break;
					case "css":
						header("Content-type: text/css");
						break;
					case ".js":
						header("Content-type: text/javascript");
						break;
				}
				
				header("Status: 404 Not Found");
				
				if ($errorIfNotFound)
					$this->loadErrorPage(404);
				
				return false;
			}
		}
		
		function VirtualPage($row_page)
		{
			$this->loadVirtualPage($row_page["theme"], $row_page["view"], $row_page["title"], $row_page["file"], $row_page["html"], $row_page["reqlevel"]);
		}
		
		function panelLoad($page)
		{
			$this->chiori->initModules(array(
				"com.chiorichan.modules.users" => array(
					"scripts" => array(
						"login-form" => "?request=admin&opts=/login",
						"login-post" => "?request=admin&opts="
					),
					"table" => "users",
					"db-mode" => "sql"
					),
				"com.chiorichan.modules.template" => array(
					"title" => "Chiori Framework Administration",
					"source" => "/pages"
					)
				));
				
				$this->TemplateLoad("", "", "com.chiorichan.themes.default", "com.chiorichan.views.default");
				
				//$this->template->loadPage($page);
		}
		
		/* Initilize a Page Load using Database Entries */
		function loadVirtualPage($theme = "com.chiorichan.themes.default", $view = "com.chiorichan.views.default", $title = "", $file = "/index.php", $html = "", $reqlevel = -1)
		{
			$this->chiori->Debug1("Template: Loading from db. Theme: \"" . $theme . "\" View: \"" . $view . "\" Userlevel: " . $reqlevel . " Query: \"" . $_SERVER["QUERY_STRING"] . "\" Title: \"" . $title . "\"");
			
			$authorized = (isset($this->chiori->com->chiorichan->modules->users)) ? $this->chiori->com->chiorichan->modules->users->PageLoad($reqlevel) : true;
			
			$root = $this->config["source"];
			if (substr($root, 0, 1) != "/" && !empty($root)) $root = "/" . $root;
			
			if (substr($file, 0, 1) != "/" && !empty($file)) $file = "/" . $file;
			
			foreach($this->config["protected"] as $deriv)
			{
				if (substr($deriv, 0, 1) != "/" && !empty($root)) $deriv = "/" . $deriv;
				if (substr($file, 0, strlen($deriv)) == $deriv)
				{
					$this->chiori->Panic(500, "Configuration prohibits the viewing of this source file.");
				}
			}
			
			if (is_dir(__ROOT__ . $file) && !empty($file)) $file = $file . "/index.php";
			
			if ($authorized)
			{
				if (!empty($file) && file_exists(__ROOT__ . $root . $file))
				{
					$source = $this->OBFileReader(__ROOT__ . $root . $file);
					$echo_please = true;
				}
				elseif (!empty($html)) // Check for the following
				{
					$source = $html; // Read HTML from sql entry
				}
				else // On failure create a Framework Panic
				{
					$this->chiori->Panic(500, "Chiori Framework has had trouble loading the requested page. Please notify administrators ASAP.");
				}
				
				if (!empty($this->titleOverWrite)) $title = $this->titleOverWrite;
				
				if (empty($theme)) // Check if a template is not defined
				{
					if ($echo_please)
					{
						echo($source); // Output without eval'ing the code, end of routine
					}
					else
					{
						$this->chiori->exeCode($source); // Run the code though our eval engine, end of routine
					}
				}
				else
				{
					$this->TemplateLoad($source, $title, $theme, $view, $docRoot); // Hand proccess over to template loader, end of routine
				}
			}
			else // Log user level failure in Framework Logs, end of routine
			{
				$this->chiori->Info("ChioriTemplate: User failed required level check under subroutine \"loadVirtualPage\"");
			}			
		}
		
		/* Override Default Site Title */
		function setSiteTitle($title)
		{
			$this->config["title"] = $title;
		}
		
		/* Override Default Page Title */
		function setPageTitle($title)
		{
			$this->titleOverWrite = $title;
		}
		
		public function OBFileReader ($filename)
		{
			/*
			 * Read and execute php file and return in a string.
			 */
			
			@eval($this->globals()); // Load all global string into userspace.
			
			if (!file_exists($filename)) return ""; // Check for existing filename.
			$chiori = $this->chiori; // Create the Parent fw string
			ob_start(); // Start Output Buffer Session.
			include($filename); // Include requested file to to be captured by ob.
			$result = ob_get_contents(); // Retreive output buffer contents.
			ob_end_clean(); // Erase ob contents.
			return $result; // Return output to requesting subroutine.
		}
		
		function globals ()
		{
			$disallowed = array("GLOBALS", "_POST", "_GET", "_REQUEST", "_SERVER", "_FILES", "_COOKIE");
			$vars = array();
			foreach($GLOBALS as $k => $v)
			{
				if (!in_array($k, $disallowed))
					$vars[] = "$".$k;
			}
			return "global ".  join(",", $vars).";";
		}
		
		public function CaptureHTML($StartNew = false)
		{
			eval($this->globals());
			
			if (!$this->InSession || $StartNew)
			{
				$this->InSession = true;
				ob_start();
			}
			else
			{
				$output = ob_get_contents(); // Retreive output buffer contents.
				ob_end_clean(); // Erase ob contents.
				$this->InSession = false;
			}
			
			return $output;
		}
		
		public function LoadHeadFile($filename)
		{
			if (file_exists($filename))
			{
				$handle = fopen($filename, "r");
				$source = fread($handle, filesize($filename));
				fclose($handle);
				
				foreach ($this->chiori->paths as $key => $val)
				{
					$keys[] = "%" . $key . "%";
					$vals[] = $val;
				}

				$source = str_replace($keys, $vals, $source);
				
				$this->chiori->exeCode($source);
			}
		}
		
		function ReadHeadFile($filename)
		{
			if (file_exists($filename))
			{
				$handle = fopen($filename, "r");
				$source = fread($handle, filesize($filename));
				fclose($handle);
				
				foreach ($this->chiori->paths as $key => $val)
				{
					$keys[] = "%" . $key . "%";
					$vals[] = $val;				
				}

				return str_replace($keys, $vals, $source);
			}
		}
		
		function TemplateLoad($source, $pageTitle = "", $theme = "com.chiorichan.themes.default", $view = "com.chiorichan.views.default", $docType = "", $obMarker = "<!-- PAGE DATA -->", $loadCommand = true)
		{
			/*
			 * This function should be the called to start the load of requested template.
			 */
			
			if (empty($docType)) $docType = "html";
			if (empty($theme)) $theme = "com.chiorichan.themes.default";
			if (empty($view)) $view = "com.chiorichan.views.default";
			if (empty($pageTitle)) $pageTitle = $this->config["pageTitle"];
			
			$themeName = substr($theme, strrpos($theme, ".") + 1);
			
			if (!$this->chiori->isSite($theme)) $theme = $this->chiori->componentSite() . ".themes." . $themeName;
			
			// html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\"
			echo("<!DOCTYPE " . $docType . ">\n");
			echo("<html xmlns=\"http://www.w3.org/1999/xhtml\">\n");
			echo("<head>\n");
			echo("<meta charset=\"utf-8\">\n");
			
			$chiori = $this->chiori;
			echo (empty($pageTitle)) ? "<title>" . $this->config["title"] . "</title>\n" : "<title>" . $pageTitle . " - " . $this->config["title"] . "</title>\n" ;

			foreach ($this->config["metatags"] as $val) echo $val;
			
			if ($loadCommand)
				$chiori->includeComponent($chiori->componentSite($theme) . ".includes.common");
				
			$chiori->includeComponent($chiori->componentSite($theme) . ".includes." . $themeName);
			
			foreach ($this->headExtras as $val) echo $val;
			
			//echo("<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\n");
			echo("</head>\n");
			echo("<body>\n");
			
			$theme_data = "";
			$view_data = "";
			
			if (!empty($theme)) $theme_data = $chiori->includeComponent($theme, true);
			if (!empty($view)) $view_data = $chiori->includeComponent($view, true);		
			
			$contents = $theme_data;
			if (!empty($view_data))
			{
				if (strpos($view_data, $obMarker) !== false)
				{
					$contents = str_replace($obMarker, trim($view_data), $contents);
				}
				else
				{
					$contents = $contents . $view_data;
				}
			}
			
			if (strpos($contents, $obMarker) !== false)
			{
				$contents = str_replace($obMarker, trim($source), $contents);
			}
			else
			{
				$contents = $contents . $source;
			}
			
			/*
			foreach ($this->OBSections as $key => $val)
			{
				if (!empty($key))
				{
					$keys[] = "<!-- " . $key . " -->";
					$vals[] = $val;
				}				
			}

			$contents = str_replace($keys, $vals, $contents);
			*/
			
			$this->chiori->exeCode($contents);
			
			echo "</body>\n";
			echo "</html>";
		}
		
		public function SetConfig($CfgArray)
		{
			$this->config = array_merge($this->config, $CfgArray);
		}
		
		public function SetMeta($desc = "", $keys = array())
		{
			if (!empty($desc)) $this->metatags[] = "<meta name=\"description\" content=\"" . $desc . "\"/>";
			if (!empty($keys))
			{
				foreach ($keys as $val => $dummy)
				{
					$words .= "," . $val;
				}
				$this->metatags[] = "<meta name=\"keywords\" content=\"" . substr($words, 1) . "\"/>";
			}
		}
		
		function statusMessage($status)
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
	}
?>
