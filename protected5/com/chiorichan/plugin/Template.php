<?php
	__Require("com.chiorichan.exception.TemplateException");

	Class Template extends Plugin
	{
		public function __construct($config = null)
		{
			parent::__construct();
			$this->pluginName = "Chiori Template";
			$this->pluginPackage = "com.chiorichan.plugin.Template";
		}
		
		public function rewriteVirtual ( $page = "", $domain = "", $return = false )
		{
			if ( getFramework()->getDatabaseEngine()->getPDO(CONFIG_FW) == null )
				return false;
			
			if ( strpos($page, "?") !== false )
				$page = substr( $page, 0, strpos( $page, "?" ) );
			
			$rows = array();
			if (empty($domain))
				$domain = $_SERVER["SERVER_NAME"]; 
			
			getFramework()->getServer()->Debug3("&5Searching for \"" . $domain . "/" . $page . "\" within FW Database.");
			
			$site = "";
			if (substr_count($domain, ".") > 1)
			{
				$site = substr($domain, 0, strrpos($domain, ".", -6));
				$domain = substr($domain, strrpos($domain, ".", -6) + 1);
			}
				
			$r1 = getFramework()->getDatabaseEngine()->select("pages", array(array("site" => "", "|site" => $site), "domain" => $domain), array(), CONFIG_FW);
			$r2 = getFramework()->getDatabaseEngine()->select("pages", array(array("site" => "", "|site" => $site), "domain" => ""), array(), CONFIG_FW);
			
			$result = array_merge($r1, $r2);
				
			if ($result !== false)
			{
				foreach($result as $row)
				{
					$row["preg"] = false;
					$arr = $row["page"];
						
					if (substr($page, 0, 1) == "/") $page = substr($page, 1);
					if (substr($arr, 0, 1) == "/") $arr = substr($arr, 1);
					
					$src = preg_split("/[.\/]/", $page);
					$desc = preg_split("/[.\/]/", $arr);
					
					//$src = explode("/", $page);
					//$desc = explode("/", $arr);
			
					$whole_match = true;
					for ($i = 0; $i < count($desc); $i++)
					{
						//getFramework()->getServer()->Debug1("Step " . $i . " - " . $arr . ": " . $src[$i] . " ==> " . $desc[$i] . " ...");
						if (preg_match("/\[([a-zA-Z0-9]+)=\]/", $desc[$i], $match))
						{
							$exp = "[" . $match[1] . "=]";
							$lrtn = substr($desc[$i], 0, strpos($desc[$i], $exp));
							if (!$rrtn = substr($desc[$i], strlen($exp) - strpos($desc[$i], $exp)))
								$rrtn = "";
		
							if ((empty($lrtn) || substr($src[$i], 0, strlen($lrtn)) == $lrtn) && (empty($rrtn) || substr($src[$i], 0 - strlen($rrtn)) == $rrtn))
							{
								$op = str_replace($lrtn, "", $src[$i]);
								$op = str_replace($rrtn, "", $op);
						
								$GLOBALS[$match[1]] = $op;
								$_GET[$match[1]] = $op;
								$_POST[$match[1]] = $op;
								$_REQUEST[$match[1]] = $op;
								//getFramework()->getServer()->Debug1("MATCH-PREG");
								$row["preg"] = true;
							}
							else
							{
								//getFramework()->getServer()->Debug1("NO-MATCH");
								$whole_match = false;
								break;
							}
						}
						elseif ($src[$i] == $desc[$i])
						{
							//getFramework()->getServer()->Debug1("MATCH");
						}
						else
						{
							//getFramework()->getServer()->Debug1("NO-MATCH");
							$whole_match = false;
							break;
							}
						}
							
					if ($whole_match)
						$rows[] = $row;
				}
			}
			
			if (count($rows) > 1)
			{
				for ($i = 0; $i < count($rows); $i++)
				{
					if ($rows[$i]["preg"] && count($rows) > 1)
						unset($rows[$i]);
				}
			}
			
			if (count($rows) > 0)
			{
				$rows = array_merge($rows);
				if ( $return )
					return $rows[0];
				
				$this->loadPage($rows[0]["theme"], $rows[0]["view"], $rows[0]["title"], $rows[0]["file"], $rows[0]["html"], $rows[0]["reqlevel"]);
				return true;
			}
			
			if ( !$return )
				throw new TemplateException("Failed to find a Virtual Page.");
			
			getFramework()->getServer()->Warning("&4Failed to find a Virtual Page.");
			return false;
		}
		
		public function loadPage ( $theme = "com.chiorichan.themes.default", $view = "com.chiorichan.views.default", $title = "", $file = "/index.php", $html = "", $reqlevel = -1 )
		{
			$echo_please = false;
			
			// TODO: Check if webuser is allowed to see this page.
			$authorized = true;
			
			$pages = getFramework()->getSource();
			if ( substr($pages, 0, 1) != "/" && !empty($pages) )
				$pages = "/" . $pages;
			if ( substr($file, 0, 1) != "/" && !empty($file) )
				$file = "/" . $file;
			
			// Check that requested page is not listed in the protected array.
			foreach ( getFramework()->getProtected() as $path )
			{
				if (substr($path, 0, 1) != "/" && !empty($path)) $path = "/" . $path;
				if (substr($file, 0, strlen($path)) == $path)
				{
					throw new TemplateException("Configuration prohibits the viewing of this file.");
					return false;
				}
			}
			
			$file = __ROOT__ . $pages . $file;
			
			if ( is_dir( $file ) )
			{
				if ( file_exists( $file . "/index.php" ) )
				{
					$file = $file . "/index.php";
				}
				else if ( file_exists( $file . "/index.html" ) )
				{
					$file = $file . "/index.html";
				}
				else
				{
					$file = "";
				}
			}
			
			if ( $authorized )
			{
				if ( !empty( $html ) )
				{
					$source = $html;
				}
				else if ( file_exists( $file ) )
				{
					//$source = $this->fileReader( $file );
					$source = getFramework()->getServer()->fileReader( $file );
					$echo_please = true;
				}
				else
				{
					throw new TemplateException("Template Plugin has encountered problems loading the requested page.");
					return false;
				}
				
				if ( empty( $theme ) )
				{
					if ( $echo_please )
					{
						echo $source;
					}
					else
					{
						try
						{
							getFramework()->getServer()->runSource( $source );
						}
						catch ( Exception $e )
						{
							throw $e;
						}
					}
				}
				else
				{
					$this->createPage ( $source, $title, $theme, $view );
				}
			}
			else
			{
				throw new TemplateException("Webuser has failed required userlevel.");
				return false;
			}
		}
		
		public function createPage ( $source, $pageTitle = "", $theme = "com.chiorichan.themes.default", $view = "com.chiorichan.views.default", $docType = "", $obMarker = "<!-- PAGE DATA -->", $loadCommon = true )
		{
			if (empty($docType)) $docType = "html";
			if (empty($theme)) $theme = "com.chiorichan.themes.default";
			if (empty($view)) $view = "com.chiorichan.views.default";
			
			$themeName = getFramework()->getFunctions()->getPackageName( $theme );
			
			if ( strpos( $theme, "." ) === false )
				$theme = trim("com.chiorichan.themes." . $themeName);
			
			if ( strpos( $view, "." ) === false )
				$theme = trim("com.chiorichan.views." . getFramework()->getFunctions()->getPackageName( $view ));
			
			// html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\"
			echo("<!DOCTYPE " . $docType . ">\n");
			echo("<html xmlns=\"http://www.w3.org/1999/xhtml\">\n");
			echo("<head>\n");
			echo("<meta charset=\"utf-8\">\n");
			
			echo ( empty( $pageTitle ) )
			? "<title>" . getFramework()->getSiteTitle() . "</title>\n"
			: "<title>" . $pageTitle . " - " . getFramework()->getSiteTitle() . "</title>\n" ;
			
			foreach (getFramework()->getMetaTags() as $val)
				echo $val;
			
			if ($loadCommon)
				getFramework()->getServer()->includePackage( getFramework()->getFunctions()->getPackageDomain( $theme ) . ".includes.common" );
			
			getFramework()->getServer()->includePackage( getFramework()->getFunctions()->getPackageDomain( $theme ) . ".includes." . $themeName );
			
			//echo("<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\n");
			echo("</head>\n");
			echo("<body>\n");
			
			$theme_data = (empty($theme)) ? "" : $theme_data = getFramework()->getServer()->includePackage($theme, true);
			$view_data = (empty($view)) ? "" : $view_data = getFramework()->getServer()->includePackage($view, true);
			
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
			
			try
			{
				getFramework()->getServer()->runSource( $contents );
			}
			catch ( Exception $e )
			{
				throw $e;
			}
				
			echo "</body>\n";
			echo "</html>";
		}
		
		public function localFile ( $page = "" )
		{
			getFramework()->getServer()->Debug3("&5Searching for \"" . $domain . "/" . $page . "\" within local filespace.");
			$filename = $_SERVER["DOCUMENT_ROOT"] . "/" . $page;
			
			if (file_exists($filename) && !is_dir($filename))
			{
				chdir ( dirname ( $filename ) );
			
				if (substr($filename, -3) == "php")
				{
					include($filename);
				}
				else
				{
					if (false) //filesize($filename) > 268435456)
					{
						// File too large to load
						// TODO: Possiblely Implement Later
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
			
						getFramework()->getServer()->Debug2("Outputing from local file \"" . $filename . "\" with mime type \"" . $ftype . "\"");
			
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
				
				die();
			}
			elseif (is_dir($filename))
			{
				getFramework()->getServer()->Warning("&4We found a folder that matched the url inquiry but Folders Are Not Fully Implemented!");
				// TODO: Implement Folder Listings
			
				if ( substr( $page, -1 ) != "/" )
				{
					getFramework()->getServer->sendRedirect( "/" . $page . "/" );
					die ();
				}
			
				return $this->localFile ( $page . "/index.php" );
			}
			else
			{
				getFramework()->getServer()->Warning("&4Unable to locate a file that matched the provided URL.");
			
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
			
				throw new TemplateException("Unable to locate a file that matched the provided URL.");
				return false;
			}
		}
	}