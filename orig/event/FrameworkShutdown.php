<?php
	Class FrameworkShutdown extends Event
	{
		function __construct()
		{
			parent::__construct();
			
			$this->functionName = "FrameworkShutdownEvent";
			$this->eventName = "Framework Shutdown";
		}
	}