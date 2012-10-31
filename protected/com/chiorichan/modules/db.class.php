<?
	/**
	 * (C) 2011 Chiori Greene
	 * All Rights Reserved.
	 * Author: Chiori Greene
	 * E-Mail: chiorigreene@gmail.com
	 * 
	 * This class is intellectual property of Chiori Greene and can only be distributed in whole with its parent
	 * framework which is known as Chiori Framework.
	 * 
	 * Keep software like this free and open source by following the authors wishes.
	 * 
	 * Class Name: Chiori DB
	 * Version: 1.0.0 Offical Release
	 * Released: December 19th, 2012
	 * Description: This class offers a abstraction layer for any Database functions needed to the Chiori Framework.
	 * If is possible to load this function more then once as a connection to the Framework DB and the Site DB.
	 */

class com_chiorichan_modules_db
{
	private $db;
	private $chiori;
	private $config = array(
		"type" => "SQL",
		"username" => "",
		"password" => ""
	);
	
	function __construct ($parentClass, $defaultConfig = array())
	{
		// Save reference copy of parent class
		$this->chiori = $parentClass;
		
		// Save configuration data
		$this->config = array_merge($this->config, $defaultConfig);
		$config =& $this->config;
		
		// Attempt DB Connection
		try
		{
			$this->db = new PDO("mysql:host=" . $config["host"] . ";dbname=" . $config["database"], $config["username"], $config["password"], array(PDO::ATTR_PERSISTENT => true));
			$op = true;
		}
		catch (PDOException $e)
		{
			//$this->chiori->Panic(500, $e->getMessage());
			die("Internal Server Error: Cound not connect to Database.");
			$op = false;
		}
		
		// Report connection result
		$this->chiori->initDebug("ChioriDB: Attempting connection to database \"" . $config["database"] . "\" on \"" . $config["host"] . "\"", $op);
		
		// Change Encoding type to UTF8
		$this->db->exec("SET CHARACTER SET utf8");
	}
	
	function __destruct()
	{
		$this->db = null;
	}
	
	function DBImage($table, $where = array(), $thumbwidth = 0, $thumbheight = 0, $imgVal = "img", $type = "jpg", $zoom = false)
	{
		if (empty($table)) $table = "images";
		
		$whr = array();
		foreach ($where as $key => $val)
		{
			$whr[] = "`" . $key . "` = '" . $val . "'";
		}
		
		$where = $this->array2Where($whr);
		
		$row = $this->selectOne($table, $where);
		
		if ($row === false)
		{
			$row = $this->SelectOne("images", "ID = '0'");
			if ($row === false) return false;
		}
		
		$db_img = imagecreatefromstring($row[$imgVal]);
		
		if ($db_img === false || empty($db_img)) return false;
		
		$width = imagesx($db_img);
		$height = imagesy($db_img);
		
		if ($thumbwidth > 0 && $thumbheight > 0 && $zoom = true)
		{
			if (($thumbwidth * $height / $width) > $thumbheight)
			{
				$thumbheight = $thumbwidth * $height / $width;
			}
			else
			{
				$thumbwidth = $thumbheight * $width / $height;
			}
		}
		elseif ($thumbwidth > 0 && $thumbheight > 0)
		{
			if (($thumbwidth * $height / $width) > $thumbheight)
			{
				$thumbwidth = $thumbheight * $width / $height;
			}
			else
			{
				$thumbheight = $thumbwidth * $height / $width;
			}
		}
		else
		{
			if($thumbheight=='' || $thumbheight==0)
			{
				if($thumbwidth=='' || $thumbwidth==00)
				{
					$thumbwidth = 150;
				}
				$thumbheight = $thumbwidth * $height / $width;
			}
			
			if($thumbwidth=='' || $thumbwidth==0)
			{
				if($thumbheight=='' || $thumbheight==0)
				{
					$thumbheight = 150;
				}
				$thumbwidth = $thumbheight * $width / $height;
			}
		}
		
		$thumb = @imagecreatetruecolor ($thumbwidth, $thumbheight) or die ("Can't create Image!");

		imagecopyresized($thumb, $db_img, 0, 0, 0, 0, $thumbwidth, $thumbheight, $width, $height);
		
		switch ($type) {
		case "jpg":
		header("Content-Type: image/jpeg");
		imagejpeg($thumb,"",85);
		break;
		case "gif":
		header("Content-Type: image/gif");
		imagegif($thumb,"",85);
		break;
		case "png":
		header("Content-Type: image/png");
		imagepng($thumb,"",85);
		break;
		}
		
		imagedestroy($db_img);
		
		return true;
	}
	
	public function select($table, $where = "", $opt = array()) // Options: limit, offSet, orderBy, groupBy and fields
	{
		if (is_array($where))
		{
			$tmp = "";
			$whr = "";
			foreach($where as $key => $val)
			{
				if (is_array($val))
				{
					$opr = "AND";
					if (strpos($key, "|") !== false) $opr = "OR";
					if (strpos($key, "&") !== false) $opr = "AND";
					
					$tmp2 = "";
					foreach($val as $key => $val)
					{
						$opr = "AND";
						if (strpos($key, "|") !== false) $opr = "OR";
						if (strpos($key, "&") !== false) $opr = "AND";
						$key = str_replace(array("|", "&"), "", $key);
						
						$tmp2 = "`" . $key . "` = '" . $val . "'";
						$tmp .= (empty($tmp)) ? $tmp2 : $sub_whr . " " . $opr . " " . $tmp2;
					}
					
					$whr = (empty($whr)) ? "(" . $tmp . ")" : $whr . " " . $opr . " (" . $tmp . ")";
				}
				else
				{
					$opr = "AND";
					if (strpos($key, "|") !== false) $opr = "OR";
					if (strpos($key, "&") !== false) $opr = "AND";
					$key = str_replace(array("|", "&"), "", $key);
					$tmp = "`" . $key . "` = '" . $val . "'";
					$whr = (empty($whr)) ? $tmp : $whr . " " . $opr . " " . $tmp;
				}
			}
		}
		else
		{
			if ($where != null)
				$whr = $where;
		}
		
		$opt_def = array(
			"limit"	=> 0,
			"offSet" => 0,
			"orderBy" => "",
			"groupBy" => "",
			"fields" => "*",
			"debug" => false,
			"debugr" => false
		);
		
		$opt = array_merge($opt_def, $opt);
		
		$limit = ($opt["limit"]>0) ? " LIMIT " . $opt["offSet"] . ", " . $opt["limit"] : "";
		$orderby = (empty($opt["orderBy"])) ? "" : " ORDER BY " . $opt["orderBy"];
		$groupby = (empty($opt["groupBy"])) ? "" : " GROUP BY " . $opt["groupBy"];
		
		$where = (empty($whr)) ? "" : " WHERE " . $whr;
		
		$query = "SELECT " . $opt["fields"] . " FROM `" . $table . "`" . $where . $groupby . $orderby . $limit;
		
		$this->SQLInjectionDetection($query);
		
		$out = $this->db->query($query);
		
		if ($out === false)
		{
			$error = $this->db->errorInfo();
			$this->chiori->Warning("ChioriDB: Making SELECT query \"" . $query . "\" which returned with error: \"" . $error[2] . "\".");
			return array();
		}
		
		$result = array();
		foreach($out as $row)
		{
			$result[] = $row;
		}
		
		$this->chiori->Debug1("ChioriDB: Making SELECT query \"" . $query . "\" which returned " . count($result) . " row(s).");
		
		if ($opt["debug"]) var_dump($query);
		if ($opt["debugr"]) var_dump($result);
		
		return $result;
	}
	
	public function selectOne($table, $where = "")
	{
		$result = $this->select($table, $where, array("limit" => 1));
		
		if (count($result) < 1)
			return false;
		
		return $result[0];
	}
	
	public function array2Where ($where = array(), $limiter = "AND", $where_str = "")
	{
		if (!is_array($where)) return false;
		
		foreach ($where as $val)
		{
			if (empty($where_str))
			{
				$where_str = $val;
			}
			else
			{
				$where_str .= " " . $limiter . " " . $val;
			}
		}
		
		return $where_str;
	}
	
	public function query($query, $debug = false)
	{
		$this->chiori->Debug1("ChioriDB: Manual Query: " . $query);

		$this->SQLInjectionDetection($query);
		
		$out = $this->db->query($query);
		
		if ($out === false)
		{
			$error = $this->db->errorInfo();
			$this->chiori->Warning("ChioriDB: Making query \"" . $query . "\" which returned with error: \"" . $error[2] . "\".");
			return array();
		}
		
		$result = array();
		foreach($out as $row)
		{
			$result[] = $row;
		}
		
		return $result;
	}

	public function SQLInjectionDetection($QueryString)
	{
		/*
		 * Checks Query String for Attempted SQL Injection by Checking for Certain Commands After the First 6 Characters.
		 * Warning: This Check Will Return True (or Positive) if You Check A Query That Inserts an Image.
		 */
		
		// $this->chiori->Debug1("Checking Query for SQL Injection. Debug \"" . $QueryString . "\".");
		
		$QueryString = strtoupper($QueryString);
		$QuerySafe = false;

		$unSafeWords = array("SELECT", "UPDATE", "DELETE", "INSERT", "UNION", "--");

		$Splice = substr($QueryString, 0, 6);
		foreach ($unSafeWords as $value)
		{
			if ($Splice == $value)
			{
				$QuerySafe = true;
			}
		}

		if (!$QuerySafe)
			$this->chiori->Panic(400, "SQL Injection Detected! Notify administrators ASAP. Debug \"" . $QueryString . "\".");

		$Splice = substr($QueryString, 6);
		foreach ($unSafeWords as $value)
		{
			if(strpos($Splice, $value) !== false)
			{
				$QuerySafe = false;
			}
		}

		if (!$QuerySafe)
			$this->chiori->Panic(400, "SQL Injection Detected! Notify administrators ASAP. Debug \"" . $QueryString . "\".");
	}
	
	function update($table, $data, $where = "", $limit = 0, $DisableInjectionCheck = false) // $data accepts JSON String and Array.
	{
		if (is_array($where))
		{
			$tmp = "";
			$whr = "";
			foreach($where as $key => $val)
			{
				if (is_array($val))
				{
					$opr = "AND";
					if (strpos("|", $key) !== false) $opr = "OR";
					if (strpos("&", $key) !== false) $opr = "AND";
					
					$tmp2 = "";
					foreach($val as $key => $val)
					{
						$opr = "AND";
						if (strpos("|", $key) !== false) $opr = "OR";
						if (strpos("&", $key) !== false) $opr = "AND";
						$key = str_replace(array("|", "&"), "", $key);
						
						$tmp2 = "`" . $key . "` = '" . $val . "'";
						$tmp = (empty($tmp)) ? $tmp2 : $sub_whr . " " . $opr . " " . $tmp2;
					}
					
					$whr = (empty($whr)) ? "(" . $tmp . ")" : $whr . " " . $opr . " (" . $tmp . ")";
				}
				else
				{
					$opr = "AND";
					if (strpos("|", $key) !== false) $opr = "OR";
					if (strpos("&", $key) !== false) $opr = "AND";
					$key = str_replace(array("|", "&"), "", $key);
					$tmp = "`" . $key . "` = '" . $val . "'";
					$whr = (empty($whr)) ? $tmp : $whr . " " . $opr . " " . $tmp;
				}
			}
		}
		else
		{
			$whr = $where;
		}
		
		$limit = ($limit>0) ? " LIMIT " . $limit : "";
		$whr = (empty($whr)) ? "" : " WHERE " . $whr;

		$setarr = "";

		if(!is_array($data))
		{
			$data = json_decode($data, true);
			if(is_null($data)) return false;
		}

		foreach( $data as $key => $value ) {
			if(empty($setarr))
			{
				$setarr = "`" . $key . "` = '" . $value . "'";
			}else{
				$setarr = $setarr . ", `" . $key . "` = '" . $value . "'";
			}
		}
		
		$query = "UPDATE " . $table . " SET " . $setarr . $whr .  $limit . ";";
		
		if (!$DisableInjectionCheck) $this->SQLInjectionDetection($query);
		
		$result = $this->db->exec($query);
		if ($result !== false)
		{
			$this->chiori->Debug1("ChioriDB: Making UPDATE query \"" . $query . "\" which affected " . $result . " rows.");
			return true;
		}
		else
		{
			$error = $this->db->errorInfo();
			$this->chiori->Debug1("ChioriDB: Making UPDATE query \"" . $query . "\" which had no affect on the database, Error: " . $error[2] . ".");
			return false;
		}
	}
	
	function delete($table, $where = "", $limit = 0)
	{
		if (is_array($where))
		{
			$tmp = "";
			$whr = "";
			foreach($where as $key => $val)
			{
				if (is_array($val))
				{
					$opr = "AND";
					if (strpos("|", $key) !== false) $opr = "OR";
					if (strpos("&", $key) !== false) $opr = "AND";
					
					$tmp2 = "";
					foreach($val as $key => $val)
					{
						$opr = "AND";
						if (strpos("|", $key) !== false) $opr = "OR";
						if (strpos("&", $key) !== false) $opr = "AND";
						$key = str_replace(array("|", "&"), "", $key);
						
						$tmp2 = "`" . $key . "` = '" . $val . "'";
						$tmp = (empty($tmp)) ? $tmp2 : $sub_whr . " " . $opr . " " . $tmp2;
					}
					
					$whr = (empty($whr)) ? "(" . $tmp . ")" : $whr . " " . $opr . " (" . $tmp . ")";
				}
				else
				{
					$opr = "AND";
					if (strpos("|", $key) !== false) $opr = "OR";
					if (strpos("&", $key) !== false) $opr = "AND";
					$key = str_replace(array("|", "&"), "", $key);
					$tmp = "`" . $key . "` = '" . $val . "'";
					$whr = (empty($whr)) ? $tmp : $whr . " " . $opr . " " . $tmp;
				}
			}
		}
		else
		{
			$whr = $where;
		}
		
		$limit = ($limit>0) ? " LIMIT " . $limit : "";
		$whr = (empty($whr)) ? "" : " WHERE " . $whr;
		$query = "DELETE FROM " . $table . $whr .  $limit . ";";
		$this->SQLInjectionDetection($query);
		$this->chiori->Debug1("ChioriDB: " . $query);
		
		$result = $this->db->exec($query);
		if ($result !== false)
		{
			$this->chiori->Debug1("ChioriDB: Making DELETE query \"" . $query . "\" which affected " . $result . " rows.");
			return $result;
		}
		else
		{
			$error = $this->db->errorInfo();
			$this->chiori->Debug1("ChioriDB: Making DELETE query \"" . $query . "\" which had no affect on the database, Error: " . $error[2] . ".");
			return false;
		}
	}
	
	function insert($table, $data, $DisableInjectionCheck = false) // $data accepts JSON String and Array.
	{
		$keys = "";
		$values = "";
		
		if(!is_array($data))
		{
			$data = json_decode($data, true);
			if(is_null($data)) return 0;
		}
		
		foreach( $data as $key => $value ) {
			$key = $this->escape($key);
			$value = $this->escape($value);
			
			if(empty($keys))
			{
				$keys = "`" . $key . "`";
			}else{
				$keys = $keys . ", `" . $key . "`";
			}
			
			if(empty($values))
			{
				$values = "'" . $value . "'";
			}else{
				$values = $values . ", '" . $value . "'";
			}
			
		}
		
		$query = "INSERT INTO " . $table . " (" . $keys . ") VALUES (" . $values . ");";
		
		if (!$DisableInjectionCheck && mb_strlen($query, "latin1") < 255) $this->SQLInjectionDetection($query);
		
		$result = $this->db->exec($query);
		if ($result !== false)
		{
			$this->chiori->Debug1("ChioriDB: Making INSERT query \"" . $query . "\" which affected " . $result . " rows.");
			return true;
		}
		else
		{
			$error = $this->db->errorInfo();
			$this->chiori->Debug1("ChioriDB: Making INSERT query \"" . $query . "\" which had no affect on the database, Error: \"" . $error[2] . "\".");
			return false;
		}
	}
	
	/* Easy function to give easy query escaping not filtering */
	public function escape($str) {	return addslashes($str); }
}
?>
