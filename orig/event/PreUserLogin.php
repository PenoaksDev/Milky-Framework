<?
	Class PreUserCallEvent
	{
		/*
		 * Called before anything is done about a user request.
		 */
		
		public $ipaddr;
		
		function __construct($ipaddr)
		{
			parent::__construct();
			
			$this->ipaddr = $ipaddr;
		}
	}