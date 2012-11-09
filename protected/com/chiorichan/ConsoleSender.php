<?
	__require("com.chiorichan.Colors");
	
	Class ConsoleSender
	{
		public function sendMessage(String $msg)
		{
			echo Colors::translateAlternateColors($msg) . Colors::RESET;
		}
	}