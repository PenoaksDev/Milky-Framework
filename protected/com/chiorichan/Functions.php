<?php
	class Functions
	{
		public function getPackageName ($package)
		{
			if ( $package == null || empty($package) )
				return false;
			
			return substr($package, strrpos($package, ".") + 1);
		}
		
		public function getPackagePath ()
		{
			if ( $package == null || empty($package) )
				return false;
			
			return substr($package, 0, strrpos($package, "."));
		}
	}