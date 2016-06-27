<?
	__package ("com.chiorichan.plugin");

	Class Plugin
	{
		protected $pluginName = "{Plugin Error}";
		protected $pluginPackage = "com.chiorichan.plugin";

		public function __construct()
		{
			$this->pluginName = "Unnamed Plugin";
			$this->pluginPackage = "com.chiorichan.plugin";
		}
		
		public function getPluginName ()
		{
			return $this->pluginName;
		}
		
		public function getPackage ()
		{
			return $this->pluginPackage;
		}
		
		// EventHandler: Called when 
		public function PreUserLoginEvent(PreUserCallEvent $event)
		{
			
		}
	}