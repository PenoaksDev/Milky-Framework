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
			
		}
		
		public function errorHandler ( Exception $e )
		{
			
		}
	}