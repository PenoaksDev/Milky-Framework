<?
	class Event
	{
		protected $functionName;
		protected $eventName;
		private $created = 0;
		
		public function getFunctionName()
		{
			return $this->functionName;
		}
		
		public function getEventName()
		{
			return $this->eventName;
		}
		
		function __construct()
		{
			$this->created = time();
			
			$this->functionName = null; // Define event function
			$this->eventName = null; // Define event name
		}
	}