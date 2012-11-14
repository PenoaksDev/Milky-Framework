<?
	__Require("com.chiorichan.Colors");
	
	Class DaemonSender
	{
		public function sendMessage( string $msg )
		{
			$this->rawData( Colors::translateAlternateColors($msg) . Colors::RESET );
		}
		
		public function sendDebug( string $msg, $level = LOG_DEBUG )
		{
			$this->rawData( Colors::translateAlternateColors($msg) . Colors::RESET );
		}
		
		public function sendException( string $msg, $level = LOG_ERR )
		{
			$this->rawData( Colors::translateAlternateColors($msg) . Colors::RESET );
		}
		
		// TODO: Initate connection with daemon process and send information.
		
		public function rawData (string $message)
		{
			$length = 120;
			
			if ($handle = fopen("/var/log/chiori.log", "a"))
			{
				$op = array();

				do
				{
					if (strlen($message) > $length)
					{
						$op[] = substr($message, 0, $length);
						$message = substr($message, $length);
					}
					else
					{
						$op[] = $message;
						$message = "";
					}
				}
				while (!empty($message));
				
				//$log .= $op[0] . str_repeat(" ", $length - strlen($message)) . " --> " . $msg[0] . " " . $this->logLevelVar($msg[3]) . " " . $msg[2] . str_repeat(" ", 6 - strlen($msg[2])) . " " . $msg[1] . "\n";
				
				$log = $op[0] . "\n";
				
				if (count($op) > 1)
				{
					for ($x=1;$x<=count($op);$x++)
					{
						if (!empty($op[$x]))
							$log .= $op[$x] . "\n";
					}
				}
				
				fwrite($handle, $log);
				fclose($handle);
			}
		}
	}