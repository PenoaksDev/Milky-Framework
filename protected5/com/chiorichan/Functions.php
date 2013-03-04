<?php
	class Functions
	{
		public function getPackageName ( $package )
		{
			if ( $package == null || empty($package) )
				return false;
			
			return substr($package, strrpos($package, ".") + 1);
		}
		
		public function getPackagePath ( $package )
		{
			if ( $package == null || empty($package) )
				return false;
			
			return substr($package, 0, strrpos($package, "."));
		}
		
		public function getPackageDomain ( $package )
		{
			if ( $package == null || empty($package) )
				return false;
				
			return substr($package, 0, strpos($package, ".", 4));
		}
		
		public function exceptionHandler ( Exception $e )
		{
			if ( $e->getCode() != E_NOTICE && $e->getCode() != E_DEPRECATED && $e->getCode() != E_ERROR && $e->getCode() != E_WARNING )
			{
				print_r( $e );
				//print_r( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ) );
				print_r( $e->getTrace() );
				//exit;
			}
		}
		
		public function errorHandler ( Exception $e )
		{
			
		}
		
		
		function cleanArray($Arr, $AllowedKeys)
		{
			return array_intersect_key($Arr, array_flip($AllowedKeys));
		}
		
		function formatPhone($phone)
		{
			$phone = preg_replace("/[^0-9]/", "", $phone);
	
			if(strlen($phone) == 7)
				return preg_replace("/([0-9]{3})([0-9]{4})/", "$1-$2", $phone);
			elseif(strlen($phone) == 10)
				return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "($1) $2-$3", $phone);
			else
				return $phone;
		}
		
		function createUUID ($seed = "")
		{
			return md5( $this->createGUID( $seed ) );
		}
		
		function createGUID ($namespace = "")
		{
			static $guid = "";
			$uid = uniqid("", true);
			$data = $namespace;
			$data .= $_SERVER["REQUEST_TIME"];
			$data .= $_SERVER["HTTP_USER_AGENT"];
			$data .= $_SERVER["LOCAL_ADDR"];
			$data .= $_SERVER["LOCAL_PORT"];
			$data .= $_SERVER["REMOTE_ADDR"];
			$data .= $_SERVER["REMOTE_PORT"];
			$hash = strtoupper(hash("ripemd128", $uid . $guid . md5($data)));
			
	    	$guid = substr($hash,  0,  8);
	    	$guid .= "-" . substr($hash,  8,  4);
	    	$guid .= "-" . substr($hash,  12,  4);
	    	$guid .= "-" . substr($hash,  16,  4);
	    	$guid .= "-" . substr($hash,  20,  12);
	    	
			return $guid;
	    }
	    
	    public function createTable($tableArray, $headerArray = "", $tableID = "")
	    {
	    	$x = 0;
	    	echo("<table id=\"" . $tableID . "\" class=\"altrowstable\">");
	    
	    	if (is_array($headerArray) && count($headerArray) > 0)
	    	{
	    		echo("<tr>");
	    		foreach($headerArray as $col)
	    		{
	    			echo("<th>" . $col . "</th>");
	    		}
	    		echo("</tr>");
	    	}
	    
	    	foreach($tableArray as $row)
	    	{
	    		$class = ($x % 2 == 0) ? "evenrowcolor" : "oddrowcolor";
	    		echo("<tr id=\"" . $row["rowId"] . "\" rel=\"" . $row["metaData"] . "\" class=\"" . $class . "\">");
	    		
	    		if (is_array($row))
	    		{
	    			$row["metaData"] = null;
	    			$row["rowId"] = null;
	    			
	    			$cc = 0;
	    			foreach($row as $col)
	    			{
	    				if ( !is_null( $col ) )
	    				{
	    					$subclass = (empty($col)) ? " emptyCol" : "";
	    
	    					echo("<td id=\"col_" . $cc . "\" class=\"" . $subclass . "\">" . $col . "</td>");
	    					$cc++;
	    				}
	    			}
	    		}
	    		else
	    		{
	    			echo("<td style=\"text-align: center; font-weight: bold;\" class=\"" . $class . "\" colspan=\"" . count($headerArray) . "\">" . $row . "</td>");
	    		}
	    		echo("</tr>");
	    		$x++;
	    	}
	    	echo("</table>");
	    }
	}