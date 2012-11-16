<?php
	class TemplateException extends Exception
	{
		// Redefine the exception so message isn't optional
		public function __construct($message = null, $code = null, Exception $previous = null)
		{
			if ( $message == null )
				$message = "There was a template exception within the Chiori Framework!";
			
			if ( $code == null )
				$code = -562465416515361;
			
			parent::__construct($message, $code, $previous);
		}
		
		public function __toString() 
		{
			return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
		}
	}