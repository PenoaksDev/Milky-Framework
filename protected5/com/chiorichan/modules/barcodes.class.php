<?
	/**
	 * (C) 2012 Chiori Greene
	 * All Rights Reserved.
	 * Author: Chiori Greene
	 * E-Mail: chiorigreene@gmail.com
	 * 
	 * This class is intellectual property of Chiori Greene and can only be distributed in whole with its parent
	 * framework which is known as Chiori Framework.
	 * 
	 * Keep software like this free and open source by following the authors wishes.
	 * 
	 * Class Name: Chiori Functions
	 * Version: 1.0.0 Offical Release
	 * Released: July 6th, 2012
	 */

	require(dirname(__FILE__) . "/barcodes/BCGFont.php");
	require(dirname(__FILE__) . "/barcodes/BCGColor.php");
	require(dirname(__FILE__) . "/barcodes/BCGDrawing.php");
	require(dirname(__FILE__) . "/barcodes/BCGcode128.barcode.php");
	
	require(__THIRD__ . "/phpqrcode/qrlib.php");
	
	class com_chiorichan_modules_barcodes
	{
		public $chiori;
	
		function __construct ($parentClass)
		{
			$this->chiori = $parentClass;
		}
		
		function generate128 ($str)
		{
			$font = new BCGFont('./barcodes/font/Arial.ttf', 18);
			$color_black = new BCGColor(0, 0, 0);
			$color_white = new BCGColor(255, 255, 255);
			 
			// Barcode Part
			$code = new BCGcode128();
			$code->setLabel(BCGBarcode1D::AUTO_LABEL);
			$code->setScale(2);
			$code->setThickness(30);
			$code->setForegroundColor($color_black);
			$code->setBackgroundColor($color_white);
			$code->setFont($font);
			$code->setStart(NULL);
			$code->setTilde(true);
			$code->parse($str);
			 
			// Drawing Part
			$drawing = new BCGDrawing('', $color_white);
			$drawing->setBarcode($code);
			$drawing->draw();
			 
			header('Content-Type: image/png');
			 
			$drawing->finish(BCGDrawing::IMG_FORMAT_PNG);
		}
		
		function qrcode ($str)
		{
			QRcode::png($str);
		}
	}

?>