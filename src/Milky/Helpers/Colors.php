<? namespace Milky\Helpers;

/**
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
Class Colors
{
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

	static function translateAlternateColors( $textToTranslate )
	{
		$colors = [];

		$colors["&r"] = self::RESET;
		$colors["&0"] = self::BLACK;
		$colors["&8"] = self::DARK_GRAY;
		$colors["&1"] = self::BLUE;
		$colors["&9"] = self::LIGHT_BLUE;
		$colors["&2"] = self::GREEN;
		$colors["&a"] = self::LIGHT_GREEN;
		$colors["&5"] = self::CYAN; //
		$colors["&6"] = self::LIGHT_CYAN; //
		$colors["&4"] = self::RED;
		$colors["&c"] = self::LIGHT_RED;
		$colors["&p"] = self::PURPLE; //
		$colors["&l"] = self::LIGHT_PURPLE; //
		$colors["&b"] = self::BROWN; //
		$colors["&e"] = self::YELLOW;
		$colors["&7"] = self::LIGHT_GRAY;
		$colors["&f"] = self::WHITE;

		foreach ( $colors as $key => $value )
		{
			$textToTranslate = str_replace( $key, $value, $textToTranslate );
		}

		return $textToTranslate;
	}

	public function values()
	{
		$colors = [];

		$colors["RESET"] = self::RESET;
		$colors["BLACK"] = self::BLACK;
		$colors["DARK_GRAY"] = self::DARK_GRAY;
		$colors["BLUE"] = self::BLUE;
		$colors["LIGHT_BLUE"] = self::LIGHT_BLUE;
		$colors["GREEN"] = self::GREEN;
		$colors["LIGHT_GREEN"] = self::LIGHT_GREEN;
		$colors["CYAN"] = self::CYAN;
		$colors["LIGHT_CYAN"] = self::LIGHT_CYAN;
		$colors["RED"] = self::RED;
		$colors["LIGHT_RED"] = self::LIGHT_RED;
		$colors["PURPLE"] = self::PURPLE;
		$colors["LIGHT_PURPLE"] = self::LIGHT_PURPLE;
		$colors["BROWN"] = self::BROWN;
		$colors["YELLOW"] = self::YELLOW;
		$colors["LIGHT_GRAY"] = self::LIGHT_GRAY;
		$colors["WHITE"] = self::WHITE;

		$colors["HL_BLACK"] = self::HL_BLACK;
		$colors["HL_RED"] = self::HL_RED;
		$colors["HL_GREEN"] = self::HL_GREEN;
		$colors["HL_YELLOW"] = self::HL_YELLOW;
		$colors["HL_BLUE"] = self::HL_BLUE;
		$colors["HL_MAGENTA"] = self::HL_MAGENTA;
		$colors["HL_CYAN"] = self::HL_CYAN;
		$colors["HL_LIGHT_GRAY"] = self::HL_LIGHT_GRAY;

		return $colors;
	}

	public function toString()
	{
		return implode( ",", $this->values() );
	}

	public function __toString()
	{
		return implode( ",", $this->values() );
	}
}
