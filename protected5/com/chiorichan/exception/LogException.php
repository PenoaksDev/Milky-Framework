<?php
	class LogException extends Exception
	{
		// Redefine the exception so message isn't optional
		public function __construct($message = null, $code = null, Exception $previous = null)
		{
			if ( $message == null )
				$message = "There was a logging system exception within the Chiori Framework!";
			
			if ( $code == null )
				$code = -562465416515352;
			
			parent::__construct($message, $code, $previous);
		}
		
		public function __toString() 
		{
			return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
		}
	}