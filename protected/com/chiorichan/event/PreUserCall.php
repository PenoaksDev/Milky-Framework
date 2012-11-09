<?
	Class PreUserCallEvent
	{
		/*
		 * Called before anything is done about a user request.
		 */
		
		public $ipaddr;
		
		function __construct($ipaddr)
		{
			$this->ipaddr = $ipaddr;
		}
	}