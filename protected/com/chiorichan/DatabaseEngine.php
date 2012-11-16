<?php
	class DatabaseEngine
	{
		protected $pdo = array();
		protected $activePDO = CONFIG_SITE;
		
		function __construct()
		{
			//getFramework()->getConfig->getArray
		}
		
		function __destruct()
		{
			$this->pdo = null;
		}
		
		// Search PDO Array for matching PDOs
		public function searchPDO () {}
		
		public function setActivePDO ($activePDO)
		{
			if ( is_string( $activePDO ) )
				$this->activePDO = $activePDO;
		}
		
		public function buildPDO ($database, $id = null, $type = "mysql", $username = "", $password = "", $hostname = "localhost", $prefix = "")
		{
			// TODO: Check that all vars are acceptable.
			
			try
			{
				// TODO: Add support for sqlite databases next.
				$pdo = new PDO("mysql:host=" . $hostname . ";dbname=" . $database, $username, $password, array(PDO::ATTR_PERSISTENT => true));
			}
			catch (PDOException $e)
			{
				// TODO: Change to getServer()
				throw $e;
				return false;
			}
			
			getFramework()->getServer()->Debug("&1Made successful connection to \"" . $database . "\".");
			
			$pdo->exec("SET CHARACTER SET utf8");
			
			if ( $id != null && ( is_string($id) || is_long($id) || is_int($id) ) )
			{
				$this->pdo[$id] = $pdo;
			}
			else
			{
				$this->pdo[] = $pdo;
			}
			
			return true;
		}
		
		public function getPDO( $id = null )
		{
			if ( $id != null && is_string( $id ) )
			{
				return $this->pdo[ $id ];
			}
			else
			{
				return $this->pdo[ $this->activePDO ];
			}
		}
		
		public function select($table, $where = "", $opt = array(), $pdo = CONFIG_SITE) // Options: limit, offSet, orderBy, groupBy and fields
		{
			$pdo = $this->getPDO($pdo);
			
			if ( $pdo == null )
				return false;
			
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
			
			$out = $pdo->query($query);
			
			if ($out === false)
			{
				$error = $pdo->errorInfo();
				getFramework()->getServer()->Warning("&4Making SELECT query \"" . $query . "\" which returned with error: \"" . $error[2] . "\".");
				return array();
			}
			
			$result = array();
			foreach($out as $row)
			{
				$result[] = $row;
			}
			
			getFramework()->getServer()->Debug2("&5Making SELECT query \"" . $query . "\" which returned " . count($result) . " row(s).");
			
			if ($opt["debug"]) var_dump($query);
			if ($opt["debugr"]) var_dump($result);
			
			return $result;
		}
		
		public function selectOne($table, $where = "", $pdo = CONFIG_SITE)
		{
			$result = $this->select($table, $where, array("limit" => 1), $pdo);
			
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
		
		public function query($query, $debug = false, $pdo = CONFIG_SITE)
		{
			$pdo = $this->getPDO($pdo);
				
			if ( $pdo == null )
				return false;
			
			$this->chiori->Debug1("ChioriDB: Manual Query: " . $query);
	
			$this->SQLInjectionDetection($query);
			
			$out = $pdo->query($query);
			
			if ($out === false)
			{
				$error = $pdo->errorInfo();
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
	
		/**
		 * Checks Query String for Attempted SQL Injection by Checking for Certain Commands After the First 6 Characters.
		 * Warning: This Check Will Return True (or Positive) if You Check A Query That Inserts an Image.
		 */
		public function SQLInjectionDetection($QueryString)
		{
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
		
		function update($table, $data, $where = "", $limit = 0, $pdo = CONFIG_SITE, $DisableInjectionCheck = false) // $data accepts JSON String and Array.
		{
			$pdo = $this->getPDO($pdo);
				
			if ( $pdo == null )
				return false;
			
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
			
			$result = $pdo->exec($query);
			if ($result !== false)
			{
				$this->chiori->Debug1("ChioriDB: Making UPDATE query \"" . $query . "\" which affected " . $result . " rows.");
				return true;
			}
			else
			{
				$error = $pdo->errorInfo();
				$this->chiori->Debug1("ChioriDB: Making UPDATE query \"" . $query . "\" which had no affect on the database, Error: " . $error[2] . ".");
				return false;
			}
		}
		
		function delete($table, $where = "", $limit = 0, $pdo = CONFIG_SITE)
		{
			$pdo = $this->getPDO($pdo);
				
			if ( $pdo == null )
				return false;
			
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
			
			$result = $pdo->exec($query);
			if ($result !== false)
			{
				$this->chiori->Debug1("ChioriDB: Making DELETE query \"" . $query . "\" which affected " . $result . " rows.");
				return $result;
			}
			else
			{
				$error = $pdo->errorInfo();
				$this->chiori->Debug1("ChioriDB: Making DELETE query \"" . $query . "\" which had no affect on the database, Error: " . $error[2] . ".");
				return false;
			}
		}
		
		function insert($table, $data, $pdo = CONFIG_SITE, $DisableInjectionCheck = false) // $data accepts JSON String and Array.
		{
			$pdo = $this->getPDO($pdo);
				
			if ( $pdo == null )
				return false;
			
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
			
			$result = $pdo->exec($query);
			if ($result !== false)
			{
				$this->chiori->Debug1("ChioriDB: Making INSERT query \"" . $query . "\" which affected " . $result . " rows.");
				return true;
			}
			else
			{
				$error = $pdo->errorInfo();
				$this->chiori->Debug1("ChioriDB: Making INSERT query \"" . $query . "\" which had no affect on the database, Error: \"" . $error[2] . "\".");
				return false;
			}
		}
		
		/* Easy function to give easy query escaping not filtering */
		public function escape($str) {	return addslashes($str); }
	}