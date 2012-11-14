<?
	Class Plugin
	{
		protected $pluginName = "{Plugin Error}";

		public function getPluginName ()
		{
			return $this->pluginName;
		}
		
		// EventHandler: Called when 
		public function PreUserLoginEvent(PreUserCallEvent $event)
		{
			
		}
	}