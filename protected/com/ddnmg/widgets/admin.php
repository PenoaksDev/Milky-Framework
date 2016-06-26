<?
$user = getFramework()->getUserService();
	if ( $user->getUserState() )
	{
		$edomain = substr( $user->getString("email"), -10 );
		if ( !empty( $edomain ) && $edomain == "@ddnmg.com" )
		{
			$webmail = "<form id=\"form\" method=\"post\" target=\"_blank\" action=\"https://www.ddnmg.com/mail/index.php\">";
			$webmail .= "<input type=\"hidden\" name=\"autodetect\" value=\"0\">";
			$webmail .= "<input type=\"hidden\" name=\"logged\" value=\"1\">";
			$webmail .= "<input type=\"hidden\" name=\"_action\" value=\"login\">";
			$webmail .= "<input type=\"hidden\" name=\"_task\" value=\"login\">";
			$webmail .= "<input type=\"hidden\" name=\"_timezone\" value=\"-6\">";
			$webmail .= "<input type=\"hidden\" name=\"_url\" value=\"\">";
			$webmail .= "<input type=\"hidden\" name=\"_user\" value=\"" . $user->getString("email") . "\">";
			$webmail .= "<input type=\"hidden\" name=\"_pass\" value=\"" . $user->getString("password") . "\">";
			$webmail .= "<a href=\"javascript:$('#form').submit();\">Webmail Login</a>";
			$webmail .= "</form>";
		}
		else
		{
			$webmail = "<a href=\"/mail\" target=\"blank\">Webmail Login</a>";
		}
		
		?>
			<a target="_blank" href="https://en.gravatar.com/">
				<img src="http://www.gravatar.com/avatar/<?=md5(getFramework()->getUserService()->getString("email"));?>?s=150&?f=y&d=mm" class="menuImage" />
			</a>
			<p>
				Welcome Back, <? echo ( !getFramework()->getUserService()->getString( "displayname" ) ) ? getFramework()->getUserService()->getString("fname") . " " . getFramework()->getUserService()->getString("name") : getFramework()->getUserService()->getString( "displayname" ); ?><br />
				Title: <?=getFramework()->getUserService()->getString( "displaylevel" )?>
			</p>
			<ul>
				<li class="divider"></li>
				<li class="nav-header">My Account</li>
				<li><?=$webmail?></li>
				<li><a href="?logout">Logout</a></li>
				<li class="divider"></li>
				<li class="nav-header">Administration</li>
				<li><a href="/finance/balshet">Balance Sheets</a></li>
				<li><a href="/finance/balshetnew">New Balance Sheet</a></li>
				<li><a href="/accounts/index">Locations</a></li>
				<li><a href="/accounts/users">User Logins</a></li>
			</ul>
		<?
	}
	else
	{
		?>
			<a target="_blank" href="https://en.gravatar.com/">
				<img src="http://www.gravatar.com/avatar/<?=md5("meaningless");?>?s=150&?f=y&d=mm" class="menuImage" />
			</a>
			<p>
				Welcome, Visitor!
			</p>
			<ul>
				<li class="divider"></li>
				<li class="nav-header">My Account</li>
				<li><a href="/login">Login</a></li>
			</ul>
		<?	
	}
?>