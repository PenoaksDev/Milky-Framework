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
	 * Released: December 3rd, 2011
	 * Description: This class is used to give easy access to small yet powerful functions that would other wise clog the main class.
	 */

$this->nameSpaceInclude("com.chiorichan.modules.xml");

class com_chiorichan_modules_functions
{
	public $chiori;

	function __construct ($parentClass)
	{
		$this->chiori = $parentClass;
	}
	
	function createGUID ($namespace = "")
	{
		static $guid = "";
		$uid = uniqid("", true);
		$data = $namespace;
		$data .= $_SERVER["REQUEST_TIME"];
		$data .= $_SERVER["HTTP_USER_AGENT"];
		$data .= $_SERVER["LOCAL_ADDR"];
		$data .= $_SERVER["LOCAL_PORT"];
		$data .= $_SERVER["REMOTE_ADDR"];
		$data .= $_SERVER["REMOTE_PORT"];
		$hash = strtoupper(hash("ripemd128", $uid . $guid . md5($data)));
		
    	$guid = substr($hash,  0,  8);
    	$guid .= "-" . substr($hash,  8,  4);
    	$guid .= "-" . substr($hash,  12,  4);
    	$guid .= "-" . substr($hash,  16,  4);
    	$guid .= "-" . substr($hash,  20,  12);
    	
		return $guid;
    }
    
	function randomNum ($length = 8, $numbers = true, $letters = false, $allowed_chars = array())
	{
		if ($numbers) $allowed_chars = array_merge($allowed_chars, array("1","2","3","4","5","6","7","8","9","0"));
		if ($letters) $allowed_chars = array_merge($allowed_chars, array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z"));
		
		for ($i = 1; $i <= $length; $i++) {
			$rtn = $rtn . $allowed_chars[mt_rand (0, count($allowed_chars)-1)];
		}
		
		$this->chiori->Debug1("Random number generated, result \"" . $rtn . "\".");
		
		return $rtn;
	}
	
	function redirectURL($StrURL, $RedirectCode = 301, $AutoRedirect = true)
	{
		header("HTTP/1.1 " . $RedirectCode . ' ' . $this->statusMessage($RedirectCode));
		if ($AutoRedirect)
		{
			if (headers_sent())
			{
				$this->dummyRedirect($StrURL);
			}
			else
			{
				header("Location: " . $StrURL);
			}
		}
		else
		{
			// Needs attention!!!
//			$this->SpecialPage($RedirectCode, "The Request URL has been relocated to: " . $StrURL . "<br />Please change any bookmarks to reference this new location.");
		}
	}
	
	public function createTable($tableArray, $headerArray = "", $tableID = "")
	{
		$x = 0;
		echo("<table id=\"" . $tableID . "\" class=\"altrowstable\">");
		
		if (is_array($headerArray) && count($headerArray) > 0)
		{
			echo("<tr>");
			foreach($headerArray as $col)
			{
				echo("<th>" . $col . "</th>");
			}
			echo("</tr>");
		}
		
		foreach($tableArray as $row)
		{
			$class = ($x % 2 == 0) ? "evenrowcolor" : "oddrowcolor";
			echo("<tr id=\"" . $row["rowId"] . "\" rel=\"" . $row["metaData"] . "\" class=\"" . $class . "\">");
			
			$row["metaData"] = null;
			$row["rowId"] = null;
			
			if (is_array($row))
			{
				$cc = 0;
				foreach($row as $col)
				{
					if ( !is_null( $col ) )
					{
						$subclass = (empty($col)) ? " emptyCol" : "";
						
						echo("<td id=\"col_" . $cc . "\" class=\"" . $subclass . "\">" . $col . "</td>");
						$cc++;
					}
				}
			}
			else
			{
				echo("<td style=\"text-align: center; font-weight: bold;\" class=\"" . $class . "\" colspan=\"" . count($headerArray) . "\">" . $row . "</td>");
			}
			echo("</tr>");
			$x++;
		}
		echo("</table>");
	}
	
	function getCloud( $data = array(), $minFontSize = null, $maxFontSize = null, $clickURL = "/search?q=" ) // $clickURL = (Empty = "No Click", Tag will be appended to end of url.)
	{
		if (is_null($minFontSize)) $minFontSize = 12;
		if (is_null($maxFontSize)) $maxFontSize = 30;
		
		$minimumCount = min( array_values( $data ) );
		$maximumCount = max( array_values( $data ) );
		$spread       = $maximumCount - $minimumCount;
		$cloudHTML    = '';
		$cloudTags    = array();
	 
		$spread == 0 && $spread = 1;
	 
		foreach( $data as $tag => $count )
		{
			$size = $minFontSize + ( $count - $minimumCount ) 
				* ( $maxFontSize - $minFontSize ) / $spread;
			if (!empty($clickURL)) $url = "href=\"" . $clickURL . $tag . "\" ";
			$cloudTags[] = '<a style="font-size: ' . floor( $size ) . 'px' 
			. '" class="tag_cloud" ' . $url 
			. 'title="\'' . $tag  . '\' returned a count of ' . $count . '">' 
			. htmlspecialchars( stripslashes( $tag ) ) . '</a>';
		}
	 
		return join( "\n", $cloudTags ) . "\n";
	}
	
	function filterString( $Words, $FilterLevel = 1, $SpecialWords = true, $ReplaceWith = "" ) // 0 = All Words, 1 = G, 2 = PG, 3 = PG-13, 4 = NC-17
	{
		/*
		 * This function detects if a single word or array of words contain any of the restricted words below.
		 * You can also use this function to filter and/or detect simple words you might not want in tag clouds
		 * that are created from descriptions and/or titles.
		 *
		 * Function Version: 1.0.2011.1203
		 */
		 
		 $this->chiori->Debug1("Word Filtering Executed at level " . $FilterLevel . " and will replace found words with \"" . $ReplaceWith . "\".");
	
		$FilteredList = Array();
		
		$level[] = Array(
			"the",
			"a",
			"p",
			"by",
			"of",
			"in",
			"with",
			"it",
			"is",
			"to",
			"'s",
			"'t",
			"on",
			"for",
			"-"
		); // Level 0
		
		for ($i = 0; $i <= 25; $i++) $level[0][] = strtolower(chr(65 + $i)); // Add english alphabet to filter list.

		$level[] = Array(
			"crap"
		); // Level 1

		$level[] = Array(
			"shit",
			"bitch"
		); // Level 2
		
		$level[] = Array(
			"fuck"
		); // Level 3
		
		for ($i = $FilterLevel; $i <= (count($level) - 1); $i++)
		{
			$FilteredList = array_merge($FilteredList, $level[$i]);
		}
		
		if (!is_array($Words))
		{
			if (strpos($Words, ",") !== false)
			{
				$Words = explode(",", $Words);
			}
			elseif (strpos($Words, "|") !== false)
			{
				$Words = explode("|", $Words);
			}
			else
			{
				$Words = Array($Words);
			}
		}
		
		foreach ( $Words as $Word )
		{
			if ( in_array( strtolower( $Word ), $FilteredList ) ) return true;
			if ( $SpecialWords && strtolower(substr($Word, 0, 1)) == "f" && strpos($Word, "*") !== false ) return true;
			if ( $SpecialWords && strtolower(substr($Word, 0, 1)) == "s" && strpos($Word, "*") !== false ) return true;
		}
		return false;
	}
	
	function kshuffle(&$array) {
	    if(!is_array($array) || empty($array)) {
		return false;
	    }
	    $tmp = array();
	    foreach($array as $key => $value) {
		$tmp[] = array('k' => $key, 'v' => $value);
	    }
	    shuffle($tmp);
	    $array = array();
	    foreach($tmp as $entry) {
		$array[$entry['k']] = $entry['v'];
	    }
	    return true;
	}
	
	function dummyRedirect($url)
	{
		$this->chiori->Debug1("Script is making a HTTP_GET redirect to " . $url . ".");
		echo("<script>window.location = '" . $url . "';</script>");
	}
	
	function jsCall ($script, $onReady = true)
	{
		if ($onReady)
		{
			$ready1 = "$(document).ready(function () {";
			$ready2 = "});";
		}
		
		$this->chiori->Debug1("Script is making a JS Call. " . $script);
		echo("<script type=\"text/javascript\">". $ready1 . $script . $ready2 . "</script>");
	}
	
	function errorPanic($err)
	{
		$GLOBALS["lasterr"] = $err;
		$source = $this->chiori->includeComponent("com.chiorichan.pages.panic");
		$this->chiori->template->loadVirtualPage("com.chiorichan.themes.error", "", "Panic Attack", "", $source, -1);
	}
	
	function statusMessage($status)
	{
		$codes = Array(
		    100 => 'Continue',
		    101 => 'Switching Protocols',
		    200 => 'OK',
		    201 => 'Created',
		    202 => 'Accepted',
		    203 => 'Non-Authoritative Information',
		    204 => 'No Content',
		    205 => 'Reset Content',
		    206 => 'Partial Content',
		    300 => 'Multiple Choices',
		    301 => 'Moved Permanently',
		    302 => 'Found',
		    303 => 'See Other',
		    304 => 'Not Modified',
		    305 => 'Use Proxy',
		    306 => '(Unused)',
		    307 => 'Temporary Redirect',
		    400 => 'Bad Request',
		    401 => 'Unauthorized',
		    402 => 'Payment Required',
		    403 => 'Forbidden',
		    404 => 'Not Found',
		    405 => 'Method Not Allowed',
		    406 => 'Not Acceptable',
		    407 => 'Proxy Authentication Required',
		    408 => 'Request Timeout',
		    409 => 'Conflict',
		    410 => 'Gone',
		    411 => 'Length Required',
		    412 => 'Precondition Failed',
		    413 => 'Request Entity Too Large',
		    414 => 'Request-URI Too Long',
		    415 => 'Unsupported Media Type',
		    416 => 'Requested Range Not Satisfiable',
		    417 => 'Expectation Failed',
		    500 => 'Internal Server Error',
		    501 => 'Not Implemented',
		    502 => 'Bad Gateway',
		    503 => 'Service Unavailable',
		    504 => 'Gateway Timeout',
		    505 => 'HTTP Version Not Supported'
		);

		return (isset($codes[$status])) ? $codes[$status] : '';
	}
	
	function cleanArray($Arr, $AllowedKeys)
	{
		return array_intersect_key($Arr, array_flip($AllowedKeys));
	}
	
	function formatPhone($phone)
	{
		$phone = preg_replace("/[^0-9]/", "", $phone);

		if(strlen($phone) == 7)
			return preg_replace("/([0-9]{3})([0-9]{4})/", "$1-$2", $phone);
		elseif(strlen($phone) == 10)
			return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "($1) $2-$3", $phone);
		else
			return $phone;
	}
	
	// XML 2.0 Functions
	
	function xml2array2 ($xmlstr)
	{
		$doc = new DOMDocument();
		$doc->loadXML($xmlstr);
		return $this->domnode_to_array($doc->documentElement);
	}
	
	function domnode_to_array ($node)
	{
		$output = array();
		switch ($node->nodeType)
		{
			case XML_CDATA_SECTION_NODE:
			case XML_TEXT_NODE:
				$output = trim($node->textContent);
				break;
			case XML_ELEMENT_NODE:
				for ($i=0, $m=$node->childNodes->length; $i<$m; $i++)
				{
					$child = $node->childNodes->item($i);
					$v = $this->domnode_to_array($child);
					if (isset($child->tagName))
					{
						$t = $child->tagName;
						if (!isset($output[$t]))
						{
							$output[$t] = array();
						}
						$output[$t][] = $v;
					}
					elseif($v)
					{
						$output = (string) $v;
					}
				}
				if (is_array($output))
				{
					if ($node->attributes->length)
					{
						$a = array();
						foreach ($node->attributes as $attrName => $attrNode)
						{
							$a[$attrName] = (string) $attrNode->value;
						}
						$output['@attributes'] = $a;
					}
					foreach ($output as $t => $v)
					{
						if (is_array($v) && count($v)==1 && $t!='@attributes')
						{
							$output[$t] = $v[0];
						}
					}
				}
				break;
		}
		return $output;
	}
	
	// XML Functions
	
	public function Array2XML( $data, $rootNodeName = 'XML', &$xml=null )
	{
        // turn off compatibility mode as simple xml throws a wobbly if you don't.
        if ( ini_get('zend.ze1_compatibility_mode') == 1 ) ini_set ( 'zend.ze1_compatibility_mode', 0 );
        if ( is_null( $xml ) )
        {
        	$xml_head = str_replace("\\", "", "<?xml version=\"1.0\" encoding=\"UTF-8\" ?\><$rootNodeName />");
        	$xml = new SimpleXMLElement_Plus($xml_head);
        }
	
        // loop through the data passed in.
        foreach( $data as $key => $value ) {
			
			$numeric = false;
            // no numeric keys in our xml please!
            if ( is_numeric( $key ) ) {
                $numeric = true;
                $key = $rootNodeName;
            }
			
			if (strpos($key, ",")>0)
			{
				$keyarr = explode(",", $key);
				$key = $keyarr[0];
			}
			
            // delete any char not allowed in XML element names
            $key = preg_replace('/[^a-z0-9\-\_\.\:]/i', '', $key);
			
            // if there is another array found recrusively call this function
            if ( is_array( $value ) )
            {
                $node = $this->isAssoc( $value ) || $numeric ? $xml_child = $xml->addChild( $key ) : $xml;

                // recrusive call.
                if ( $numeric ) $key = 'anon';
                $this->Array2XML( $value, $key, $node );
            }
            else
            {
                // add single node.
                $value = htmlentities( $value );
                $xml_child = $xml->addChild( $key, $value );
            }
            	
			if (count($keyarr) > 0)
			{
				foreach( $keyarr as $key2 => $keyarr2 ) {
					if ($keyarr2 != $key)
					{
						$keyarr2 = explode("=", $keyarr2);
						$xml_child->addAttribute($keyarr2[0], $keyarr2[1]);
					}
				}
				$keyarr = NULL;
			}
        }
        
        return $xml->asXML();
        
	    $doc = new DOMDocument('1.0');
	    $doc->preserveWhiteSpace = false;
	    $doc->loadXML( $xml->asXML() );
	    $doc->formatOutput = true;
	    return $doc->saveXML();
	}
		
    public function toArray( $xml, $rtn_attrib = false ) {
        if ( is_string( $xml ) ) $xml = new SimpleXMLElement( $xml );
        $attributes = $xml->attributes();
        $children = $xml->children();
        if ( !$children ) return (string) $xml;
        $arr = array();
		if ($rtn_attrib)
		{
		    foreach ($attributes as $key => $node)
		    {
		    	$arr[$key] = (string) $node;
   		    }
		}
        foreach ( $children as $key => $node ) {
            $node = $this->toArray( $node );
            
            // support for 'anon' non-associative arrays
            if ( $key == 'anon' ) $key = count( $arr );

            // if the node is already set, put it into an array
            if ( isset( $arr[$key] ) ) {
                if ( !is_array( $arr[$key] ) || $arr[$key][0] == null ) $arr[$key] = array( $arr[$key] );
                $arr[$key][] = $node;
            } else {
                $arr[$key] = $node;
            }
        }
        return $arr;
    }
	
	public function isAssoc( $array ) {
		return (is_array($array) && 0 !== count(array_diff_key($array, array_keys(array_keys($array)))));
	}
		
	public function XML2Array($url, $get_attributes = 1, $priority = 'tag')
	{
	    $contents = "";
	    if (!function_exists('xml_parser_create'))
	    {
		echo("Apache is not built with the XML function to allow this script to run.");
		return array ();
	    }
	    $parser = xml_parser_create('');
	    if (!($fp = @ fopen($url, 'rb')))
	    {
		return array ();
	    }
	    while (!feof($fp))
	    {
		$contents .= fread($fp, 8192);
	    }
	    fclose($fp);
	    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
	    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	    xml_parse_into_struct($parser, trim($contents), $xml_values);
	    xml_parser_free($parser);
	    if (!$xml_values)
		return; //Hmm...
	    $xml_array = array ();
	    $parents = array ();
	    $opened_tags = array ();
	    $arr = array ();
	    $current = & $xml_array;
	    $repeated_tag_index = array ();
	    foreach ($xml_values as $data)
	    {
		unset ($attributes, $value);
		extract($data);
		$result = array ();
		$attributes_data = array ();
		if (isset ($value))
		{
		    if ($priority == 'tag')
		        $result = $value;
		    else
		        $result['value'] = $value;
		}
		if (isset ($attributes) and $get_attributes)
		{
		    foreach ($attributes as $attr => $val)
		    {
		        if ($priority == 'tag')
		            $attributes_data[$attr] = $val;
		        else
		            $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
		    }
		}
		if ($type == "open")
		{
		    $parent[$level -1] = & $current;
		    if (!is_array($current) or (!in_array($tag, array_keys($current))))
		    {
		        $current[$tag] = $result;
		        if ($attributes_data)
		            $current[$tag . '_attr'] = $attributes_data;
		        $repeated_tag_index[$tag . '_' . $level] = 1;
		        $current = & $current[$tag];
		    }
		    else
		    {
		        if (isset ($current[$tag][0]))
		        {
		            $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
		            $repeated_tag_index[$tag . '_' . $level]++;
		        }
		        else
		        {
		            $current[$tag] = array (
		                $current[$tag],
		                $result
		            );
		            $repeated_tag_index[$tag . '_' . $level] = 2;
		            if (isset ($current[$tag . '_attr']))
		            {
		                $current[$tag]['0_attr'] = $current[$tag . '_attr'];
		                unset ($current[$tag . '_attr']);
		            }
		        }
		        $last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
		        $current = & $current[$tag][$last_item_index];
		    }
		}
		elseif ($type == "complete")
		{
		    if (!isset ($current[$tag]))
		    {
		        $current[$tag] = $result;
		        $repeated_tag_index[$tag . '_' . $level] = 1;
		        if ($priority == 'tag' and $attributes_data)
		            $current[$tag . '_attr'] = $attributes_data;
		    }
		    else
		    {
		        if (isset ($current[$tag][0]) and is_array($current[$tag]))
		        {
		            $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
		            if ($priority == 'tag' and $get_attributes and $attributes_data)
		            {
		                $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
		            }
		            $repeated_tag_index[$tag . '_' . $level]++;
		        }
		        else
		        {
		            $current[$tag] = array (
		                $current[$tag],
		                $result
		            );
		            $repeated_tag_index[$tag . '_' . $level] = 1;
		            if ($priority == 'tag' and $get_attributes)
		            {
		                if (isset ($current[$tag . '_attr']))
		                {
		                    $current[$tag]['0_attr'] = $current[$tag . '_attr'];
		                    unset ($current[$tag . '_attr']);
		                }
		                if ($attributes_data)
		                {
		                    $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
		                }
		            }
		            $repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
		        }
		    }
		}
		elseif ($type == 'close')
		{
		    $current = & $parent[$level -1];
		}
	    }
	    return ($xml_array);
	}
}
