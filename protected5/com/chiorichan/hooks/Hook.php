<?
	Class Hook
	{
		private $hookName = "{Plugin Error}";
		private $hookPackage = "com.chiorichan.plugin";

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