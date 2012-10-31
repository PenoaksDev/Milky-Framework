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
	 */

class com_chikislist_modules_api extends ModuleTemplateBasic
{
	private $location = array();
	
	/* Rewards API */
	function rewardsBalance ($mobile)
	{
		if (empty($mobile))
			return false;
		
		$contact = $this->chiori->nameSpaceGet("com.chiorichan.modules.db")->selectOne("contacts_rewards", array("mobile_no" => $mobile, "locID" => $this->location["locID"]));
		if ($contact === false)
			$contact = $this->rewardsNew($mobile);
		
		if ($contact === false)
			return false;
		
		return $contact["balance"];
	}
	
	function rewardsEarn ($mobile, $amount)
	{
		if (empty($mobile))
			return false;
		
		$contact = $this->chiori->nameSpaceGet("com.chiorichan.modules.db")->selectOne("contacts_rewards", array("mobile_no" => $mobile, "locID" => $this->location["locID"]));
		if ($contact === false)
			$contact = $this->rewardsNew($mobile);
		
		if ($contact === false)
			return false;
			
		if (microtime(true) < ($contact["last_instore_check"] + 10800))
			return false;
		
		$amount = $contact["balance"] + $amount;
		
		$this->chiori->nameSpaceGet("com.chiorichan.modules.db")->insert("rewards_translog", array("mobile_no" => $contact["mobile_no"], "origin" => $this->location["locID"], "time" => microtime(true), "operation" => "Earned Points", "field1" => "new balance: " . $amount));
		
		$this->chiori->nameSpaceGet("com.chiorichan.modules.db")->update("contacts_rewards", array("balance" => $amount, "last_instore_check" => microtime(true)), array("mobile_no" => $mobile, "locID" => $this->location["locID"]));
		
		return true;
	}
	
	function rewardsClaim ($mobile, $reward) // $reward can equal either a db row array or a string that will return the reward.
	{
		if (empty($mobile))
			return false;
		
		if (!is_array($reward))
		{
			$reward = $chiori->namespaceGet("com.chiorichan.modules.db")->selectOne("rewards_redeem", array("redeemID" => $reward));
			if ($reward === false)
				return false;
		}
		
		$amount = $reward["cost"];
		
		$contact = $this->chiori->nameSpaceGet("com.chiorichan.modules.db")->selectOne("contacts_rewards", array("mobile_no" => $mobile, "locID" => $this->location["locID"]));
		if ($contact === false)
			$contact = $this->rewardsNew($mobile);
		
		if ($contact === false)
			return false;
		
		if ($contact["balance"] < $amount)
			return false;
		
		$amount = $contact["balance"] - $amount;
		
		$this->chiori->nameSpaceGet("com.chiorichan.modules.db")->insert("rewards_translog", array("mobile_no" => $contact["mobile_no"], "origin" => $this->location["locID"], "time" => microtime(true), "operation" => "Claimed Points", "field1" => "Remaining Balance: \"" .  $amount . "\" Reward ID: \"" . $reward["redeemID"] . "\""));
		
		$this->chiori->nameSpaceGet("com.chiorichan.modules.db")->update("contacts_rewards", array("balance" => $amount, "last_instore_check" => microtime(true)), array("mobile_no" => $mobile, "locID" => $this->location["locID"]));
		
		return true;
	}
	
	function rewardsNew ($mobile)
	{
		$contact = $this->chiori->nameSpaceGet("com.chiorichan.modules.db")->selectOne("contacts_rewards", array("mobile_no" => $mobile, "locID" => $this->location["locID"]));
		if ($contact === false)
		{
			$this->chiori->nameSpaceGet("com.chiorichan.modules.db")->insert("contacts_rewards", array("mobile_no" => $mobile, "locID" => $this->location["locID"], "balance" => 0, "last_instore_check" => 0));
			return $this->chiori->nameSpaceGet("com.chiorichan.modules.db")->selectOne("contacts_rewards", array("mobile_no" => $mobile, "locID" => $this->location["locID"]));
		}
		else
		{
			return false;
		}
	}
	
	function rewardsLoad ()
	{
		$this->location = $this->chiori->nameSpaceGet("com.chiorichan.modules.db")->selectOne("locations", array("ipaddress" => $_SERVER["REMOTE_ADDR"]));
		if ($this->location === false)
		{
			$this->chiori->Panic(500, "Loading of Rewards Application is blocked. Your remote address is unknown.");
		}
		
		return $this->location;
	}
	
	/* Text Messaging API */
	public function isSubscribed ($mobile, $locID)
	{
		$result = $this->chiori->sql->SelectOne("contacts", "mobile_no = '" . $mobile . "'");
		if ($result === false) return false;
		$subloc = explode("|", $result["list"]);
		
		foreach ($subloc as $val)
		{
			if (strtolower($val) == "all") return true;
			$loc = $this->chiori->sql->SelectOne("locations", "locID = '" . $val . "' || shortcode = '" . $val . "'");
			if ($loc["shortcode"] == $locID || $loc["locID"] == $locID) return true;
		}
		
		return false;
	}
	
	public function getSubscribed ($mobile)
	{
		$result = $this->chiori->nameSpaceGet("com.chiorichan.modules.db")->selectOne("contacts", "mobile_no = '" . $mobile . "'");
		if ($result === false) return false;
		return str_replace(array("+", "|"), "", explode("|", $result["list"]));
	}
	
	public function AddContact ($mobile)
	{
		if (!empty($mobile))
		{
			$result = $this->chiori->nameSpaceGet("com.chiorichan.modules.db")->selectOne("contacts", "mobile_no = '" . $mobile . "'");
			if ($result === false)
			{
				$this->chiori->nameSpaceGet("com.chiorichan.modules.db")->insert("contacts", array("mobile_no" => $mobile, "carrier" => $_GET["CarrierID"], "first_added" => date("U"), "last_changed" => date("U")));
				$this->chiori->logit(8, $mobile . " added to database.");
			}
		}
	}
	
	public function SubscribeTo ($mobile, $locID)
	{
		if (!empty($mobile))
		{
			$result = $this->chiori->sql->SelectOne("locations", "shortcode = '" . $locID . "'");
			if ($result !== false) $locID = $result["locID"];
			
			$result = $this->chiori->sql->SelectOne("contacts", "mobile_no = '" . $mobile . "'");
			if ($result === false)
			{
				$this->chiori->sql->Insert("contacts", array("mobile_no" => $mobile, "list" => trim($locID), "carrier" => $_GET["CarrierID"], "first_added" => date("U"), "last_changed" => date("U")));
			}
			else
			{
				if ($locID == "ALL")
				{
					$this->chiori->sql->Update("contacts", array("list" => "ALL", "last_changed" => date("U")), "mobile_no = '" . $mobile . "'");
				}
				else
				{
					$this->chiori->sql->Update("contacts", array("list" => trim($result["list"] . "|" . trim($locID), "|"), "last_changed" => date("U")), "mobile_no = '" . $mobile . "'");
				}
			}
			
			$this->chiori->Logger(7, $mobile . " subscribed to location code " . $locID . ".");
			return true;
		}
	}
		
	public function UnSubscribeTo ($mobile, $locID)
	{
		$result_loc = $this->chiori->sql->SelectOne("locations", "shortcode = '" . $locID . "'");
		
		$result = $this->chiori->sql->SelectOne("contacts", "mobile_no = '" . $mobile . "'");
		if ($result === false)
		{
			return false;
		}
		else
		{
			$this->chiori->sql->Update("contacts", array("list" => trim(str_replace("||", "|", str_replace(array($locID, $result_loc["locID"]), "", $result["list"]))), "last_changed" => date("U")), "mobile_no = '" . $mobile . "'");
		}
		
		$this->chiori->Logger(7, $mobile . " unsubscribed to location code " . $locID . ".");
		return true;
	}
	
	public function Stop ($mobile, $operation, $msg)
	{
		if (empty($msg))
		{
			$result = $this->getSubscribed($mobile);
			if (count($result) == 0)
			{
				$this->SendSMS($mobile, $this->GetReply("TEXT_LIST_NONE"), "API_RESPONSE");
				return false;
			}
			elseif (count($result) == 1)
			{
				$msg = $result[0];
			}
			else
			{
				$this->chiori->sql->Update("contacts", array("pending_reply" => "STOP"), "mobile_no = '" . $mobile . "'");
				$this->SendSMS($mobile, $this->GetReply("TEXT_STOP_REPLY"), "API_RESPONSE");
				return false;
			}
		}
		
		if ($msg == "ALL")
		{
			$this->chiori->sql->Update("contacts", array("list" => ""), "mobile_no = '" . $mobile . "'");
			$this->SendSMS($mobile, $this->GetReply("TEXT_STOP_SUCCESS"), "API_RESPONSE");
		}
		else
		{
			$result = $this->chiori->sql->SelectOne("locations", "locID = '" . $msg . "' || shortcode = '" . $msg . "'");
			if ($result === false)
			{
				$this->SendSMS($mobile, $this->GetReply("TEXT_STOP_INVALID"), "API_RESPONSE");
				return false;
			}
			else
			{
				if ($this->isSubscribed($mobile, $msg))
				$this->UnSubscribeTo($mobile, $msg);
				$this->SendSMS($mobile, $this->GetReply("TEXT_STOP_SUCCESS", $msg), "API_RESPONSE");
			}
		}
		return true;
	}
	
	public function Start ($mobile, $operation, $msg)
	{
		if ($msg == "ALL")
		{
			$this->SubscribeTo($mobile, $msg);
			$this->SendSMS($mobile, $this->GetReply("TEXT_NEW_SUCCESS"), "API_RESPONSE");
		}
		else
		{
			$result = $this->chiori->sql->SelectOne("locations", "locID = '" . $msg . "' || shortcode = '" . $msg . "'");
			if ($result === false)
			{
				$this->SendSMS($mobile, $this->GetReply("TEXT_NEW_INVALID"), "API_RESPONSE");
				return false;
			}
			else
			{
				if ($this->isSubscribed($mobile, $msg))
				{
					$this->SendSMS($mobile, $this->GetReply("TEXT_NEW_ERROR", $msg), "API_RESPONSE");
					return false;
				}
				else
				{
					$this->SubscribeTo($mobile, $msg);
					$this->SendSMS($mobile, $this->GetReply("TEXT_NEW_SUCCESS", $msg), "API_RESPONSE");
				}
			}
		}
		return true;
	}
	
	public function GetReply ($key, $idenifier = "")
	{
		$result_loc = $this->chiori->nameSpaceGet("com.chiorichan.modules.db")->selectOne("locations", "`shortcode` = '" . $idenifier . "'");
		$result = $this->chiori->nameSpaceGet("com.chiorichan.modules.settings")->get($key, $result_loc["locID"], false);
		
		if ($result["success"])
		{		
			if ($result["isDefault"] && !empty($idenifier))
			{
				$result = $this->chiori->nameSpaceGet("com.chiorichan.modules.settings")->get($key, $result_loc["acctID"], false);
			}
			
			return $result["value"];
		}
		else
		{
			return false;
		}
	}
	
	public function AnalyzeSMS ($mobile, $operation, $msg)
	{
		$this->chiori->logit(6, "Received SMS from: " . $mobile . ", Operation: " . $operation . ", Message: " . $msg);
		$this->chiori->nameSpaceGet("com.chiorichan.modules.db")->insert("sms_translog", array("mobile_no" => $mobile, "origin" => "API_INCOMING", "operator" => "(automated)", "msg" => $msg, "created" => date("U"), "success" => 1, "debug" => "Operation: " . $operation), true);
		
		if (empty($mobile)) return false;
		
		$retrn = true;
		$msg = strtoupper($msg);
		
		$this->AddContact($mobile);
		
		switch (strtoupper($operation))
		{
			case "START": //fix
				if (!empty($msg))
				{
					$this->Start($mobile, $operation, $msg);
				}
				else
				{
					$this->chiori->nameSpaceGet("com.chiorichan.modules.db")->update("contacts", array("pending_reply" => "START"), "mobile_no = '" . $mobile . "'");
					$this->SendSMS($mobile, $this->GetReply("TEXT_NEW_REPLY"), "API_RESPONSE");
				}
				break;
			case "STOP":
				$this->Stop($mobile, $operation, $msg);
				break;
			case "HELP":
				$this->SendSMS($mobile, $this->GetReply("TEXT_HELP") . " Options: STOP, HELP, LIST.", "API_RESPONSE");
				break;
			case "RECEIVE":
				$result = $this->chiori->nameSpaceGet("com.chiorichan.modules.db")->selectOne("contacts", "mobile_no = '" . $mobile . "'");
				switch ($msg)
				{
					case "LIST":
						if ($result === false)
						{
							$this->SendSMS($mobile, $this->GetReply("TEXT_LIST_NONE"), "API_RESPONSE");
						}
						else
						{
							$subloc = explode("|", $result["list"]);
		
							foreach ($subloc as $val)
							{
								if ($val == "ALL") $reply .= "All Locations, ";
								$loc = $this->chiori->nameSpaceGet("com.chiorichan.modules.db")->selectOne("locations", "locID = '" . $val . "' || shortcode = '" . $val . "'");
								$reply .= $loc["title"] . " - " . $loc["shortcode"] . ", ";
							}
						
							if (empty($reply))
							{
								$this->SendSMS($mobile, $this->GetReply("TEXT_LIST_NONE"), "API_RESPONSE");
							}
							else
							{
								$this->SendSMS($mobile, $reply . "Options: STOP, HELP, LIST.", "API_RESPONSE");
							}
						}
						break;
					case "THANK YOU": $this->SendSMS($mobile, "Your Welcome!", "API_RESPONSE"); break;
					case "THANKS": $this->SendSMS($mobile, "Your Welcome!", "API_RESPONSE"); break;
					default:
						switch (strtoupper($result["pending_reply"]))
						{
							case "STOP":
								if ($this->Stop($mobile, $operation, $msg))
								$this->chiori->nameSpaceGet("com.chiorichan.modules.db")->update("contacts", array("pending_reply" => ""), "mobile_no = '" . $mobile . "'");
								break;
							case "START":
								if ($this->Start($mobile, $operation, $msg))
								$this->chiori->nameSpaceGet("com.chiorichan.modules.db")->update("contacts", array("pending_reply" => ""), "mobile_no = '" . $mobile . "'");
								break;
							default:
								$this->SendSMS($mobile, $this->GetReply("TEXT_UNKNOWN"), "API_RESPONSE");
						}
				}
				break;
			default:
				$this->SendSMS($mobile, $this->GetReply("TEXT_UNKNOWN"), "API_RESPONSE");
				$retrn = false;
		}		
		
		return $retrn;

		/*
		TargetID 
		CompanyID 
		CdrID 
		CallerID 
		UserID 
		CampaignID 
		CarrierID
		MessageBody
		*/
	}
	
	public function qSMS ($mobile_list, $msg)
	{
		$mobile_list = (is_array($mobile_list)) ? $mobile_list : array($mobile_list);
		$debug_array = array();
		
		foreach ($mobile_list as $mobile)
		{
			$this->chiori->logit(6, "SMS Broadcast to: " . $mobile . ", Message: " . $msg);
			
			if (!$this->chiori->nameSpaceGet("com.chiorichan.modules.settings")->get("CHIORI_DIAGNOSTICS_MODE"))
			{
				$this->chiori->nameSpaceGet("com.chiorichan.modules.db")->insert("sms_outbox", array("mobile_no" => $mobile, "owner" => $this->chiori->nameSpaceGet("com.chiorichan.modules.users")->CurrentUser["userID"], "msg" => $msg, "created" => date("U"), "updated" => date("U")));

				$this->chiori->logit(7, "SMS added to outbox.");
				$debug_array["MOBILE_" . $mobile] = "SMS added to outbox";
			}
			else
			{
				$this->chiori->logit(6, "System is in Diagnostics Mode, So SMS Broadcast was blocked.");
				$debug_array["MOBILE_" . $mobile] = "System is in Diagnostics Mode, So SMS Broadcast was blocked.";
			}
		}
		
		return $debug_array;
	}
	
	public function SendSMS ($mobile_list, $msg, $origin = "API_SEND")
	{
		$mobile_list = (is_array($mobile_list)) ? $mobile_list : array($mobile_list);
		$debug_array = array();
		
		foreach ($mobile_list as $mobile)
		{
			$this->chiori->logit(6, "SMS Broadcast to: " . $mobile . ", Message: " . $msg);
			
			if (true)//!$this->chiori->GetSetting("CHIORI_DIAGNOSTICS_MODE"))
			{
				// $url = "http://c4.commercetel.com/C4CServices/ExternalWebServices/SendDynamicSMS.aspx";
				$url = "http://4.79.35.12/C4CServices/ExternalWebServices/SendDynamicSMS.aspx";

				$fields = array
				(
					"Login"=>"solar",
					"Password"=>"energy",
					"PhoneNumber"=>$mobile,
	//				"UserCarrier"=>"34",
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
				
				$sender = $this->chiori->users->CurrentUser["userID"];
				if (empty($sender)) $sender = "(automated)";

				$this->chiori->nameSpaceGet("com.chiorichan.modules.db")->insert("sms_translog", array("mobile_no" => $mobile, "origin" => $origin, "operator" => $sender, "msg" => $msg, "created" => microtime(true), "success" => 1, "debug" => $response), true);

				$this->chiori->logit(7, "SMS sent to URL: " . $url . ", HTTP Response: " . $response);
				$debug_array["MOBILE_" . $mobile] = "SMS sent to URL: " . $url . ", HTTP Response: " . $response;
			}
			else
			{
				$this->chiori->logit(6, "System is in Diagnostics Mode, So SMS Broadcast was blocked.");
				$debug_array["MOBILE_" . $mobile] = "System is in Diagnostics Mode, So SMS Broadcast was blocked.";
			}
		}
		
		return $debug_array;
	}
	public function incomingSMS ($mobile, $keyword, $msg = "")
	{
		$mobile = trim(str_replace("+", "", $mobile));
		if (strlen($mobile) == 11) $mobile = substr($mobile, 1);
		
		if (empty($mobile) || strlen($mobile) != 10) return "Internal Error: Invalid Mobile Number Provided.";
		
		$this->chiori->logit(6, "Received SMS from: " . $mobile . ", Keyword: " . $keyword . ", Message: " . $msg);
		
		$this->chiori->nameSpaceGet("com.chiorichan.modules.db")->insert("sms_translog", array("mobile_no" => $mobile, "origin" => "API_INCOMING", "msg" => $msg, "created" => date("U"), "operator" => "automated", "success" => 1, "debug" => "Keyword" . $keyword));
		
		$msg = strtoupper($msg);
		
		$this->AddContact($mobile);
		
		switch (strtoupper($msg))
		{
			case "UNSUB":
			case "UNSUBSCRIBE":
			case "GOODBYE":
			case "END":
			case "EXIT":
			case "CANCEL":
			case "QUIT":
			case "STOP": // Add idivigual unsubscribe in future.
				$this->chiori->sql->Update("contacts", array("list" => ""), "mobile_no = '" . $mobile . "'");
				return $this->GetReply("TEXT_STOP_SUCCESS");
				
				$result = $this->getSubscribed($mobile);
				if (count($result) == 0)
				{
					return $this->GetReply("TEXT_LIST_NONE");
				}
				elseif (count($result) == 1)
				{
					$msg = $result[0];
				}
				else
				{
					$this->chiori->nameSpaceGet("com.chiorichan.modules.db")->update("contacts", array("pending_reply" => "STOP"), "mobile_no = '" . $mobile . "'");
					return $this->GetReply("TEXT_STOP_REPLY");
				}

				break;
			case "HELP":
				return $this->GetReply("TEXT_HELP") . " Options: STOP, HELP, LIST.";
				break;
			case "THANK YOU": return "Your Welcome!"; break;
			case "THANKS": return "Your Welcome!"; break;
			case "STATUS":
				$status = "";
				$result_loc = $this->chiori->nameSpaceGet("com.chiorichan.modules.db")->select("locations");
				if ($result_loc !== false)
				{
					while ($loc = mysql_fetch_array($result_loc))
					{
						$contacts = $this->chiori->nameSpaceGet("com.chiorichan.modules.db")->select("contacts", "`list` like '%" . $loc["locID"] . "%'");
						$status .= " " . $loc["locID"] . "-" . $loc["title"] . "-" . count($contacts);
					}
				}
				
				return $status;
				break;
			case "REWARDS":
				return "Sorry, That subroutine is not implemented yet!";
				break;
			case "LIST":
				$result = $this->chiori->nameSpaceGet("com.chiorichan.modules.db")->select("locations");
				if (count($result) < 1)
				{
					return $this->GetReply("TEXT_LIST_NONE");
				}
				else
				{
					foreach ($result as $row)
					{
						$reply .= $row["title"] . " - " . $row["shortcode"] . ", ";
					}
				
					if (empty($reply))
					{
						return $this->GetReply("TEXT_LIST_NONE");
					}
					else
					{
						return $reply . "Options: STOP, HELP, LIST.";
					}
				}
			break;
			default:
				if (empty($msg))
				{
					return $this->GetReply("TEXT_NEW_REPLY");
				}
				else
				{
					return ($this->StartNew($mobile, $keyword, $msg));
				}
			break;
		}
	}
	
	public function StopNew ($mobile, $operation, $msg)
	{
		$this->chiori->nameSpaceGet("com.chiorichan.modules.db")->update("contacts", array("pending_reply" => ""), "mobile_no = '" . $mobile . "'");
		
		if (empty($msg))
		{
			$result = $this->getSubscribed($mobile);
			if (count($result) == 0)
			{
				return $this->GetReply("TEXT_LIST_NONE");
			}
			elseif (count($result) == 1)
			{
				$msg = $result[0];
			}
			else
			{
				$this->chiori->sql->Update("contacts", array("pending_reply" => "STOP"), "mobile_no = '" . $mobile . "'");
				return $this->GetReply("TEXT_STOP_REPLY");
			}
		}
		
		$result_loc = $this->chiori->nameSpaceGet("com.chiorichan.modules.db")->selectOne("locations", "`locID` = '" . $msg . "' || shortcode = '" . $msg . "'");
		$result_acc = $this->chiori->nameSpaceGet("com.chiorichan.modules.db")->selectOne("accounts", "`acctID` = '" . $msg . "'");
		
		if ($msg == "ALL")
		{
			$this->chiori->sql->Update("contacts", array("list" => ""), "mobile_no = '" . $mobile . "'");
			return $this->GetReply("TEXT_STOP_SUCCESS");
		}
		elseif ($result_loc !== false || $result_acc !== false)
		{
			if ($this->isSubscribed($mobile, $msg))
			$this->UnSubscribeTo($mobile, $msg);
			return $this->GetReply("TEXT_STOP_SUCCESS", $msg);
		}
		else
		{
			return $this->GetReply("TEXT_STOP_INVALID");
		}
	}
	
	public function StartNew ($mobile, $keyword, $msg)
	{
		$this->chiori->nameSpaceGet("com.chiorichan.modules.db")->update("contacts", array("pending_reply" => ""), "mobile_no = '" . $mobile . "'");
		
		$result_loc = $this->chiori->nameSpaceGet("com.chiorichan.modules.db")->selectOne("locations", "`locID` = '" . $msg . "' || shortcode = '" . $msg . "'");
		$result_acc = $this->chiori->nameSpaceGet("com.chiorichan.modules.db")->selectOne("accounts", "`acctID` = '" . $msg . "'");
		
		if ($msg == "ALL" || $result_loc !== false || $result_acc !== false)
		{
			if ($this->isSubscribed($mobile, $msg))
			{
				return $this->GetReply("TEXT_NEW_ERROR", $msg);
			}
			else
			{
				$this->SubscribeTo($mobile, $msg);
				return $this->GetReply("TEXT_NEW_SUCCESS", $msg);
			}
		}
		else
		{
			return $this->GetReply("TEXT_NEW_INVALID");
		}
	}
}
?>
