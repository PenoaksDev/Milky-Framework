<?php
	class UserService
	{
		protected $db;
		
		function __construct()
		{
			$this->db = getFramework()->getConfig()->getDatabase();
		}
		
		public function getUserbyName(string $userName)
		{
			if ( $userName == null || empty($userName) )
				return null;
			
			
			
		}
	}