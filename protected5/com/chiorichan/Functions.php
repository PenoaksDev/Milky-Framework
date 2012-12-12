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
			if ( $e->getCode() != E_NOTICE && $e->getCode() != E_DEPRECATED )
			{
				print_r( $e );
				print_r( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ) );
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
	}