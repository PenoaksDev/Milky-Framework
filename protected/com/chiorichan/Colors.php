<?
	Class Colors
	{
		// TODO: Expand upon this subroutine with interesting color works
		
		const RESET = "\033[0m";
		const BLACK = "\033[0;30m";
		const DARK_GRAY = "\033[1;30m";
		const BLUE = "\033[0;34m";
		const LIGHT_BLUE = "\033[1;34m";
		const GREEN = "\033[0;32m";
		const LIGHT_GREEN = "\033[1;32m";
		const CYAN = "\033[0;36m";
		const LIGHT_CYAN = "\033[1;36m";
		const RED = "\033[0;31m";
		const LIGHT_RED = "\033[1;31m";
		const PURPLE = "\033[0;35m";
		const LIGHT_PURPLE = "\033[1;35m";
		const BROWN = "\033[0;33m";
		const YELLOW = "\033[1;33m";
		const LIGHT_GRAY = "\033[0;37m";
		const WHITE = "\033[1;37m";
		
		const HL_BLACK = "\033[40m";
		const HL_RED = "\033[41m";
		const HL_GREEN = "\033[42m";
		const HL_YELLOW = "\033[43m";
		const HL_BLUE = "\033[44m";
		const HL_MAGENTA = "\033[45m";
		const HL_CYAN = "\033[46m";
		const HL_LIGHT_GRAY = "\033[47m";
		
		public function translateAlternateColor(String $textToTranslate)
		{
			$colors = array();
			
			$colors["&r"] == $this::RESET;
			$colors["&0"] == $this::BLACK;
			$colors["&8"] == $this::DARK_GRAY;
			$colors["&1"] == $this::BLUE;
			$colors["&9"] == $this::LIGHT_BLUE;
			$colors["&2"] == $this::GREEN;
			$colors["&a"] == $this::LIGHT_GREEN;
			$colors["&5"] == $this::CYAN; //
			$colors["&6"] == $this::LIGHT_CYAN; //
			$colors["&4"] == $this::RED;
			$colors["&c"] == $this::LIGHT_RED;
			$colors["&p"] == $this::PURPLE; //
			$colors["&l"] == $this::LIGHT_PURPLE; //
			$colors["&b"] == $this::BROWN; //
			$colors["&e"] == $this::YELLOW;
			$colors["&7"] == $this::LIGHT_GRAY;
			$colors["&f"] == $this::WHITE;
			
			foreach ( $colors as $key => $value )
			{
				$textToTranslate = str_replace($key, $value, $textToTranslate);	
			}
			
			return $textToTranslate;
		}
		
		public function values()
		{
			$colors = array();
				
			$colors["RESET"] == $this::RESET;
			$colors["BLACK"] == $this::BLACK;
			$colors["DARK_GRAY"] == $this::DARK_GRAY;
			$colors["BLUE"] == $this::BLUE;
			$colors["LIGHT_BLUE"] == $this::LIGHT_BLUE;
			$colors["GREEN"] == $this::GREEN;
			$colors["LIGHT_GREEN"] == $this::LIGHT_GREEN;
			$colors["CYAN"] == $this::CYAN;
			$colors["LIGHT_CYAN"] == $this::LIGHT_CYAN;
			$colors["RED"] == $this::RED;
			$colors["LIGHT_RED"] == $this::LIGHT_RED;
			$colors["PURPLE"] == $this::PURPLE;
			$colors["LIGHT_PURPLE"] == $this::LIGHT_PURPLE;
			$colors["BROWN"] == $this::BROWN;
			$colors["YELLOW"] == $this::YELLOW;
			$colors["LIGHT_GRAY"] == $this::LIGHT_GRAY;
			$colors["WHITE"] == $this::WHITE;
				
			$colors["HL_BLACK"] == $this::HL_BLACK;
			$colors["HL_RED"] == $this::HL_RED;
			$colors["HL_GREEN"] == $this::HL_GREEN;
			$colors["HL_YELLOW"] == $this::HL_YELLOW;
			$colors["HL_BLUE"] == $this::HL_BLUE;
			$colors["HL_MAGENTA"] == $this::HL_MAGENTA;
			$colors["HL_CYAN"] == $this::HL_CYAN;
			$colors["HL_LIGHT_GRAY"] == $this::HL_LIGHT_GRAY;
			
			return $colors;
		}
		
		public function toString()
		{
			return implode(",", $this->values());
		}
	}