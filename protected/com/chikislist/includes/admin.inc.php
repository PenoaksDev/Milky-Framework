<?
	$style = $chiori->com->chiorichan->modules->settings->get("USER_PANEL_THEME");
	echo("<!-- User Themes -->\n");
	echo("<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $chiori->config["aliases"]["css"] . "/themes/" . str_replace(" ", "_", strtolower($style)) . ".css\" />\n\n");
?>