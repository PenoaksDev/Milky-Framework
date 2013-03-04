<?
	Class Hook
	{
		private $hookName = "{Hook Error}";
		private $hookPackage = "com.chiorichan.hooks";

		public function __construct()
		{
			$this->hookName = "Unnamed Plugin";
			$this->hookPackage = "com.chiorichan.hooks";
		}
		
		public function getPluginName ()
		{
			return $this->pluginName;
		}
		
		public function getPackage ()
		{
			return $this->pluginPackage;
		}
	}