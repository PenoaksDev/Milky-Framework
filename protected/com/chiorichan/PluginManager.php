<?
	Class PluginManager
	{
		protected $plugins = array();
		
		public function raiseEvent (Event $event = null)
		{
			if ( $event == null )
				return false;
			
			$function = $event->getFunctionName();
			
			getFramework()->getServer()->Debug3("&4Raising Event \"" . $event->getEventName() . "\".");
			
			foreach ( $this->plugins as $plugin )
			{
				if ( method_exists($plugin, $function) )
				{
					$plugin->$function();
				}
			}
			
			return true;
		}
		
		public function raiseEventbyName (string $eventName)
		{
			$event = getFramework()->buildEvent($eventName);
			
			if ( $event == null || $event === false )
				return false;
			
			$this->raiseEvent($event);
		}
		
		public function addPlugin (Plugin $plugin = null)
		{
			if ( $plugin == null )
				return false;
			
			getFramework()->getServer()->Debug3("&1Enabling Plugin \"" . $plugin->getPluginName() . "\".");
			
			$this->plugins[] = $plugin;
			$event = getFramework()->buildEvent("pluginEnable");
			if ( $event != null )
				$plugin->raiseEvent($event);
		}
		
		public function addPluginByName (string $pluginName)
		{
			$plugin = getFramework()->buildPlugin($pluginName);
			
			if ( $plugin == null || $plugin === false )
				return false;
			
			$this->addPlugin($plugin);
		}
	}