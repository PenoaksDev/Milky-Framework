<?

Class PluginManager
{
	protected $plugins = [];

	public function raiseEvent( Event $event = null )
	{
		if ( $event == null )
			return false;

		$function = $event->getFunctionName();

		getFramework()->getServer()->Debug3( "&4Raising Event \"" . $event->getEventName() . "\"." );

		foreach ( $this->plugins as $plugin )
		{
			if ( method_exists( $plugin, $function ) )
			{
				$plugin->$function();
			}
		}

		return true;
	}

	public function raiseEventbyName( $eventName )
	{
		$event = getFramework()->createEvent( $eventName );

		if ( $event == null || $event === false )
			return false;

		$this->raiseEvent( $event );
	}

	public function getPluginbyName( $pluginName )
	{
		if ( $pluginName == null || empty( $pluginName ) )
			return null;

		if ( strpos( $pluginName, "." ) === false )
			$pluginName = "com.chiorichan.plugin." . $pluginName;

		foreach ( $this->plugins as $plugin )
		{
			if ( $plugin->getPackage() == $pluginName || $plugin->getPluginName() == $pluginName )
			{
				return $plugin;
			}
		}

		return null;
	}

	public function addPlugin( Plugin $plugin = null )
	{
		if ( $plugin == null )
			return false;

		getFramework()->getServer()->Debug3( "&1Enabling Plugin \"" . $plugin->getPluginName() . "\"." );

		$this->plugins[] = $plugin;

		$event = getFramework()->createEvent( "pluginEnable" );
		if ( $event != null )
			$plugin->raiseEvent( $event );
	}

	public function addPluginByName( $pluginName, $config = null )
	{
		$plugin = getFramework()->createPlugin( $pluginName, $config );

		if ( $plugin == null || $plugin === false )
			return false;

		$this->addPlugin( $plugin );
	}
}
