<div id="fb-root"></div>
<script>
	window.fbAsyncInit = function() {
		FB.init({
			appId      : '108085152655595',
			status     : true, 
			cookie     : true,
			xfbml      : true,
			oauth      : true,
		});
	};
	(function(d){
		var js, id = 'facebook-jssdk'; if (d.getElementById(id)) {return;}
		js = d.createElement('script'); js.id = id; js.async = true;
		js.src = "//connect.facebook.net/en_US/all.js";
		d.getElementsByTagName('head')[0].appendChild(js);
		}(document));
</script>

<script type="text/javascript">
var timeIDa;
var timeIDl;

$(document).ready(function () {
	$("#login-hover").hover(function () {
		clearTimeout(timeIDl);
		$("#floating-login-box").slideDown(500);
	}, function () {
		timeIDl = setTimeout(function () {
			$("#floating-login-box").slideUp(500);
		}, 200)
	});
	
	$("#account-hover").hover(function () {
		clearTimeout(timeIDa);
		$("#floating-account-box").slideDown(500);
	}, function () {
		timeIDa = setTimeout(function () {
			$("#floating-account-box").slideUp(500);
		}, 200)
	});
});
</script>

<div id="navi-bar-wrapper">
	<div id="body" class="div01">
		<ul>
			<li><a href="http://panel.chikislist.com">Admin Panel</a></li>
			<? if ($chiori->namespaceGet("com.chiorichan.modules.users")->CurrentUser["valid"]) { ?>
				<li><a href="http://web.chikislist.com/posts/add">Add +</a></li>
				<li id="account-hover">
					<a href="javascript: void(0);">Your Account</a>
					<div id="floating-account-box" style="display: none;">
						<?
							if ($chiori->namespaceGet("com.chiorichan.modules.users")->CurrentUser["imgID"] == "fb")
							{
								$imgURL = "https://graph.facebook.com/poszest16/picture?type=large";
							}
							else
							{
								$imgURL = "http://images.chioricloud.com/sprite/user-unknown.png";
							}
						?>
						
						<img style="width: 160px;" class="pic" src="<? echo($imgURL); ?>" />
						<p>
							<?
								echo("<h3>" . $chiori->namespaceGet("com.chiorichan.modules.users")->CurrentUser["displayname"] . "</h3>");
								echo("Title: " . $chiori->namespaceGet("com.chiorichan.modules.users")->CurrentUser["title"] . "<br />");
								echo("Userlevel: " . $chiori->namespaceGet("com.chiorichan.modules.users")->CurrentUser["displaylevel"] . "<br />");
							?>
						</p>
						
						<a class="new-button" href="javascript: void(0);">Change Profile Picture</a>
							<?
								if ($chiori->namespaceGet("com.chiorichan.modules.settings")->get("USER_CHANGE_PASSWORD"))
								echo ("<a class=\"new-button\" href=\"javascript: goto_frame('passwd')\">Change Password</a>");
								if ($chiori->namespaceGet("com.chiorichan.modules.settings")->get("USER_BETA_TESTER"))
								echo ("<a class=\"new-button\" href=\"javascript: goto_frame('mobile')\">Mobile Preferences</a>");
							?>
							<a class="new-button"href="javascript: goto_frame('settings?prefix=user')">Settings</a>
							<a class="new-button" href="http://accounts.chikislist.com">Advanced</a>
							<a class="new-button" href="?logout">Logout</a>
					</div>
				</li>
		  		<li><a href="http://accounts.chikislist.com/login?logout">Logout</a></li>
			<? } else { ?>
				<li><a href="http://accounts.chikislist.com/register">New Customer</a></li>
				<li id="login-hover">
					<a href="javascript: void(0);">Login</a>
					<div id="floating-login-box" style="display: none;">
						<form id="form_chiori" action="http://accounts.chikislist.com/login" method="post">
							<div class="rowElem"><label>Username</label></div>
							<div class="rowElem"><input autocomplete="off" name="user" size="20" type="text" /></div>
							<div class="rowElem"><label>Password</label></div>
							<div class="rowElem"><input autocomplete="off" name="pass" size="20" type="password" /></div>
							<div class="rowElem">
								<a href="http://accounts.chikislist.com/invite" class="normalize">Request Invite</a><br />
								<a href="http://accounts.chikislist.com/forgot" class="normalize">Forgot Password</a>
							</div>
							<div class="rowElem"><input class="new-button" type="submit" value="Login" style="float: right;" /></div>
						</form>
						<div class="clearfix"></div>
					</div>
				</li>
			<? } ?>
			
			<li class="right no-highlight">
				<form id="search-form" class="clearfix" method="post" action="/search/en">
					<input type="text" id="search-text" name="search_value" class="required" value="Search...">
					<input type="image" src="http://images.chikislist.com/sprite/search-button.png" width="14" height="21" class="go" alt="Search" title="Search">
					<input type="hidden" name="language" value="en">  
				</form>
				
				<script type="text/javascript">
					$("#search-text").focus(function () {
						if ($(this).val() == "Search...") $(this).val("");
					});
					
					$("#search-text").blur(function () {
						if ($(this).val() == "") $(this).val("Search...");
					});
				</script>
			</li>
			
			<li class="right imgonly"><a href="#bottom"><img src="http://images.chikislist.com/sprite/bottom_arrow.png" /></a></li>
			<li class="right imgonly"><a href="#header"><img src="http://images.chikislist.com/sprite/top_arrow.png" /></a></li>
			<li class="right"><a target="_blank" href="https://www.facebook.com/ChikisList"><img src="http://images.chikislist.com/sprite/facebook.png" />Get Connected</a></li>
			<li class="right lborder"><a target="_blank" href="http://twitter.com/#!/ChikisList"><img src="http://images.chioricloud.com/sprite/twitter.png" />Follow Us</a></li>
		</ul>
	</div>
	<div id="menu" class="div01">
		<ul>
			<?
				$result_row = $chiori->namespaceGet("com.chiorichan.modules.db")->select("menu", "", array("orderBy" => "x", "groupBy" => "x"));
				if (count($result_row) > 0)
				{
					$chiori->Debug1("Menu: Found " . count($result_row) . " rows of menus.");
					foreach ($result_row as $row)
					{
						$chiori->Debug1("Menu: Found X:" . $row["x"] . " Y:" . $row["y"] . " Title:" . $row["title"] . ".");
						
						if (!empty($row["reqperm"]) && !$chiori->namespaceGet("com.chiorichan.modules.users")->GetPermission(explode("|", $row["reqperm"]))) continue;
						if (!empty($row["reqsetting"]) && $chiori->namespaceGet("com.chiorichan.modules.settings")->get(explode("|", $row["reqsetting"]))) continue;
						
						//$col_root = $chiori->db->selectOne("menu", "`x` = '" . $row["x"] . "' AND `y` = '0'");
						$col_root = $row;
						
						$result_col = $chiori->namespaceGet("com.chiorichan.modules.db")->select("menu", "`x` = '" . $row["x"] . "' AND `y` != '0'", array("orderBy" => "y"));
						$title = ($row["y"] != 0) ? "Unnamed" : $row["title"];
						
						if (count($result_col) == 0)
						{
							echo("<li>");
							// $url = ($col_root["newwin"] || $col_root["popup"]) ? $url : "javascript: goto_frame('" . $col_root["url"] . "')";
							$url = $col_root["url"];
							$target = ($col_root["newwin"]) ? " target=\"_blank\"" : "";
							echo("<a class=\"textshadow\" " . $target . " id=\"itm_" . $col_root["x"] . "_" . $col_root["y"]  . "\" href=\"" . $url . "\">" . $title . "</a>");
							if ($col_root["popup"]) $selector .= ",#itm_" . $col_root["x"] . "_" . $col_root["y"];
							$chiori->Debug1("Menu: Added X:" . $col_root["x"] . " Y:" . $col_root["y"] . " \"" . $title . "\" as root link with url \"" . $url . "\".");
						}
						else
						{
							echo("<li>");
							echo("<p class=\"textshadow\">" . $title . "</p>");
							$chiori->Debug1("Menu: Added X:" . $col_root["x"] . " Y:" . $col_root["y"] . " \"" . $title . "\" as root menu.");
						}
						
						if (count($result_col) > 0)
						{
							$selector = "";
							echo("<ul class=\"dropdown\">");
							foreach ($result_col as $col)
							{
								$chiori->Debug1("Menu: Found X:" . $col["x"] . " Y:" . $col["y"] . " Title:" . $col["title"] . ".");
								
								if (!empty($col["reqperm"]) && !$chiori->namespaceGet("com.chiorichan.modules.users")->GetPermission(explode("|", $col["reqperm"]))) continue;
								if (!empty($col["reqsetting"]) && $chiori->namespaceGet("com.chiorichan.modules.settings")->get(explode("|", $col["reqsetting"]))) continue;
								
								$url = ($col["newwin"] || $col["popup"]) ? $url : "javascript: goto_frame('" . $col["url"] . "')";
								$target = ($col["newwin"]) ? " target=\"_blank\"" : "";
								echo("<li><a" . $target . " id=\"itm_" . $col["x"] . "_" . $col["y"]  . "\" href=\"" . $url . "\">" . $col["title"] . "</a></li>");
								if ($col["popup"]) $selector .= ",#itm_" . $col["x"] . "_" . $col["y"];
								$chiori->Debug1("Menu: Added X:" . $col["x"] . " Y:" . $col["y"] . " \"" . $title . "\" as sub link with url \"" . $url . "\".");
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
			
			<!-- <li class="right imgonly no-highlight"><img style="padding: 0 16px;" src="/images/sprite/logo-favicon.png" /></li> -->
		</ul>
	</div>
</div>