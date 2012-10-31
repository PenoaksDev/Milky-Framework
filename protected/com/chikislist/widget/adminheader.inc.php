<div id="navi-bar-wrapper">
	<div id="body" class="div01">
		<ul>
			<li><a href="javascript: goto_frame('myuser');"><? echo($chiori->nameSpaceGet("com.chiorichan.modules.users")->CurrentUser["displayname"] . " (" . $chiori->nameSpaceGet("com.chiorichan.modules.users")->CurrentUser["displaylevel"] . ")"); ?></a></li>
			<li><a href="?logout">Logout</a></li>
			<li><a href="javascript: goto_frame('help');">Help</a></li>
			
			<!--
				<li class="right imgonly"><a href="#bottom"><img src="/images/sprite/bottom_arrow.png" /></a></li>
				<li class="right imgonly"><a href="#header"><img src="/images/sprite/top_arrow.png" /></a></li>
			-->
			<li class="right"><a href="http://web.chikislist.com">Web Home</a></li>
			<li class="right"><a href="javascript: goto_frame('');">Panel Home</a></li>
			<li class="right imgonly"><a href="#bottom"><img src="http://images.chikislist.com/sprite/bottom_arrow.png" /></a></li>
			<li class="right imgonly"><a href="#header"><img src="http://images.chikislist.com/sprite/top_arrow.png" /></a></li>
			<li class="right imgonly"><a target="_blank" href="https://www.facebook.com/ChikisList"><img src="http://images.chikislist.com/sprite/facebook.png" /></a></li>
			<li class="right lborder imgonly"><a target="_blank" href="http://twitter.com/#!/ChikisList"><img src="http://images.chioricloud.com/sprite/twitter.png" /></a></li>
		</ul>
	</div>
	<div id="menu" style="display: none;" class="div01">
		<ul>
			<?
				$result_row = $chiori->nameSpaceGet("com.chiorichan.modules.db")->select("admin_menu", "", array("orderBy" => "x", "groupBy" => "x"));
				if (count($result_row) > 0)
				{
					$chiori->Debug1("Admin Menu: Found " . count($result_row) . " rows of menus.");
					foreach ($result_row as $row)
					{
						$chiori->Debug1("Admin Menu: Found X:" . $row["x"] . " Y:" . $row["y"] . " Title:" . $row["title"] . ".");
						
						if (!empty($row["reqperm"]) && !$chiori->com->chiorichan->modules->users->GetPermission(explode("|", $row["reqperm"]))) continue;
						if (!empty($row["reqsetting"]) && $chiori->com->chiorichan->modules->settings->get(explode("|", $row["reqsetting"]))) continue;
						
						$col_root = $chiori->com->chiorichan->modules->db->selectOne("admin_menu", "`x` = '" . $row["x"] . "' AND `y` = '0'");
						$result_col = $chiori->com->chiorichan->modules->db->select("admin_menu", "`x` = '" . $row["x"] . "' AND `y` != '0'", array("orderBy" => "y"));
						
						$title = ($col_root === false) ? "Unnamed" : $col_root["title"];
						
						if ($result_col === false)
						{
							echo("<li>");
							//$url = ($col_root["newwin"] || $col_root["popup"]) ? $url : "javascript: goto_frame('" . $col_root["url"] . "')";
							$url = ($col_root["newwin"] || $col_root["popup"]) ? $url : "#" . $col_root["url"];
							
							$target = ($col_root["newwin"]) ? " target=\"_blank\"" : "";
							echo("<a" . $target . " id=\"itm_" . $col_root["x"] . "_" . $col_root["y"]  . "\" href=\"" . $url . "\">" . $title . "</a>");
							if ($col_root["popup"]) $selector .= ",#itm_" . $col_root["x"] . "_" . $col_root["y"];
							$chiori->Debug("Admin Menu: Added X:" . $col_root["x"] . " Y:" . $col_root["y"] . " \"" . $title . "\" as root link with url \"" . $url . "\".");
						}
						else
						{
							echo("<li>");
							echo("<p>" . $title . "</p>");
							$chiori->Debug1("Admin Menu: Added X:" . $col_root["x"] . " Y:" . $col_root["y"] . " \"" . $title . "\" as root menu.");
						}
						
						if (count($result_col) > 0)
						{
							$selector = "";
							echo("<ul class=\"dropdown\">");
							foreach ($result_col as $col)
							{
								$chiori->Debug1("Admin Menu: Found X:" . $col["x"] . " Y:" . $col["y"] . " Title:" . $col["title"] . ".");
								
								if (!empty($col["reqperm"]) && !$chiori->com->chiorichan->modules->users->GetPermission(explode("|", $col["reqperm"]))) continue;
								if (!empty($col["reqsetting"]) && $chiori->com->chiorichan->modules->settings->get(explode("|", $col["reqsetting"]))) continue;
								
								//$url = ($col["newwin"] || $col["popup"]) ? $url : "javascript: goto_frame('" . $col["url"] . "')";
								$url = ($col["newwin"] || $col["popup"]) ? $url : "#" . $col["url"];
								
								$target = ($col["newwin"]) ? " target=\"_blank\"" : "";
								echo("<li><a" . $target . " id=\"itm_" . $col["x"] . "_" . $col["y"]  . "\" href=\"" . $url . "\">" . $col["title"] . "</a></li>");
								if ($col["popup"]) $selector .= ",#itm_" . $col["x"] . "_" . $col["y"];
								$chiori->Debug1("Admin Menu: Added X:" . $col["x"] . " Y:" . $col["y"] . " \"" . $title . "\" as sub link with url \"" . $url . "\".");
							}
							echo("</ul>");
						}
						
						echo("</li>");
					}
				}	
			?>
			
			<script>
				<? if (!empty($selector)) echo("$(\"" . trim($selector, ",")) . "\").popupWindow({centerScreen: 1});"; ?>
			</script>
		</ul>
	</div>
</div>