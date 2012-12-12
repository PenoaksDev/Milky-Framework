<?
	__package ("com.chiorichan.plugin");

	Class db extends Plugin
	{
		protected $database = "";
		protected $user = "";
		protected $pass = "";
		
		public function __construct()
		{
			$this->pluginName = "Chiori Database Plugin";
		}
		
		public function select()
		{
			
		}
	}