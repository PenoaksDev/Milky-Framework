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
	 */

	class com_chikislist_modules_txt extends ModuleTemplateBasic
	{
		function incomingSMS ($mobile_no, $keyword, $msg)
		{
			$this->chiori->logit(6, "Received SMS from: " . $mobile_no . ", Keyword: " . $keyword . ", Message: " . $msg);
			$this->chiori->nameSpaceGet("com.chiorichan.modules.db")->insert("sms_translog", array("mobile_no" => $mobile_no, "origin" => "API_INCOMING", "operator" => "(automated)", "msg" => $msg, "created" => microtime(true), "success" => 1, "debug" => ""));
			
			if (empty($mobile_no)) return false;
			
			$msg = trim(strtoupper($msg));
			$keyword = trim(strtoupper($keyword));
			
			$contact = $this->addContact($mobile_no);
			
			$stop_arr = array("STOP", "END", "UNSUB", "UBSUBSCRIBE", "EXIT", "CANCEL", "QUIT", "GOODBYE");
			
			if (in_array($msg, $stop_arr))
			{
				/* Add indivigual stop in future */
				$this->chiori->nameSpaceGet("com.chiorichan.modules.db")->update("contacts", array("list" => ""), "mobile_no = '" . $mobile_no . "'");
				
				// Send Stop MF to BoomText.
				
				echo $this->replyGet("TEXT_STOP_SUCCESS");
				return true;
			}
			
			if ($msg == "HELP" || $msg == "INFO")
			{
				$this->replyGet("TEXT_HELP") . " Reply stop2stop.";
			}
			
			if (!empty($keyword))
			{
				$locID = "";
				
				if ($msg != "ALL")
				{
					$result = $this->chiori->nameSpaceGet("com.chiorichan.modules.db")->select("locations", array("keyword" => $keyword));
					
					if (count($result) < 1)
					{
						echo $this->replyGet("TEXT_NEW_INVALID");;
						return false;
					}
					
					if (count($result) == 1)
						$locID = $result[0]["locID"];
					
					foreach ($result as $loc)
					{
						if ($loc["shortcode"] == $msg)
						{
							$locID = $loc["locID"];
							$location_title = $loc["title"];
						}
					}
				}
				else
				{
					$locID = "ALL";
					$location_title = "All Locations";
				}
				
				if (empty($locID))
				{
					echo $this->replyGet("TEXT_NEW_INVALID");;
					return false;
				}
				
				$subscribed = false;
				
				foreach (explode("|", $contact["list"]) as $val)
				{
					if (strtolower($val) == "all")
						$subscribed = true;
					
					if ($val == $locID)
						$subscribed = true;
					
					if ($subscribed == true)
					{
						echo $this->replyGet("TEXT_NEW_ERROR", $locID);;
						return false;
					}
				}
				
				if ($locID == "ALL")
				{
					$this->chiori->nameSpaceGet("com.chiorichan.modules.db")->update("contacts", array("list" => "ALL", "last_changed" => date("U")), array("mobile_no" => $mobile_no));
				}
				else
				{
					$this->chiori->nameSpaceGet("com.chiorichan.modules.db")->update("contacts", array("list" => trim($contact["list"] . "|" . trim($locID), "|"), "last_changed" => date("U")), array("mobile_no" => $mobile_no));
				}
				
				$value = $this->replyGet("TEXT_NEW_SUCCESS", $locID);
				$value = str_replace("%L%", $location_title, $value);
				echo $value;
				return true;
			}
			
			$this->sendSMS("7089123702", "Misunderstood SMS: " . $msg . ", Mobile: " . $mobile_no, "API_ERROR");
		}
		
		function addContact ($mobile_no)
		{
			$result = $this->chiori->nameSpaceGet("com.chiorichan.modules.db")->selectOne("contacts", "mobile_no = '" . $mobile_no . "'");
			if ($result === false)
			{
				$this->chiori->nameSpaceGet("com.chiorichan.modules.db")->insert("contacts", array("mobile_no" => $mobile_no, "first_added" => date("U"), "last_changed" => date("U")));
				$result = $this->chiori->nameSpaceGet("com.chiorichan.modules.db")->selectOne("contacts", "mobile_no = '" . $mobile_no . "'");
				
				if ($result === false) return false; // Something went wrong!
			}
			
			return $result;
		}
		
		function replyGet ($key, $idenifier1 = "")
		{
			$result_loc = $this->chiori->nameSpaceGet("com.chiorichan.modules.db")->selectOne("locations", "`locID` = '" . $idenifier1 . "'");
			$idenifier2 = ($result_loc !== false) ? $result_loc["acctID"] : "";
			
			$result = $this->chiori->nameSpaceGet("com.chiorichan.modules.settings")->get($key, strtoupper($indenifier1), strtoupper($indenifier2), false);
			if ($result["success"])
			{
				return $result["value"];
			}
			else
			{
				return false;
			}
		}
		
		function sendSMS ($mobile_no, $message, $origin = "")
		{
			if (empty($origin)) $origin = "API_AUTOMATED";
			
			if (is_array($mobile_no))
			{
				$result = array();
				
				foreach ($mobile_no as $mobile)
				{
					$result["MOBILE_" . $mobile] = $this->sendSMS ($mobile, $message, $origin);
				}
				
				return $result;
			}
			
			$msgs = array();
			
			do
			{
				if (strlen($message) > 160)
				{
					$msgs[] = substr($message, 0, 160);
					$message = substr($message, 160);
				}
				else
				{
					$msgs[] = $message;
					$message = "";
				}
			}
			while (!empty($message));
			
			foreach ($msgs as $msg)
			{
				// $url = "http://c4.commercetel.com/C4CServices/ExternalWebServices/SendDynamicSMS.aspx";
				$url = "http://4.79.35.12/C4CServices/ExternalWebServices/SendDynamicSMS.aspx";
				
				$fields = array
				(
					"Login"=>"solar",
					"Password"=>"energy",
					"PhoneNumber"=>$mobile_no,
					"TargetID"=>"54568",
					"MessageText"=>urlencode($msg)
				);
			
				unset($fields_string);
				foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
				$fields_string = trim($fields_string, "&");
				
				$url = $url . "?" . $fields_string;
				
				$ch = curl_init();
				
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				
				$response = curl_exec($ch);
				curl_close($ch);
				
				$sender = $this->chiori->namespaceGet("com.chiorichan.modules.users")->CurrentUser["userID"];
				if (empty($sender)) $sender = "(automated)";
	
				$this->chiori->nameSpaceGet("com.chiorichan.modules.db")->insert("sms_translog", array("mobile_no" => $mobile_no, "origin" => $origin, "class" => $sender, "operator" => $sender, "msg" => $msg, "created" => microtime(true), "success" => 1, "debug" => $response), true);
	
				$this->chiori->logit(7, "SMS sent to URL: " . $url . ", HTTP Response: " . $response);
			}
			
			return array("url" => $url, "response" => $response);
		}
	}
?>