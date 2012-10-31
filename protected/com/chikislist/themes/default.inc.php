<? $chiori->nameSpaceInclude("com.chikislist.widget.header"); ?>

<div id="wrapper" class="<? echo ($chiori->namespaceGet("com.chiorichan.modules.users")->CurrentUser["valid"]) ? "div01" : "div01"; ?>">
	<div id="header">
		<a id="logolink" href="http://web.chikislist.com/" alt="Homepage"></a>
	</div>

<!-- PAGE DATA -->

	<div id="push"></div>
</div>

<? $chiori->nameSpaceInclude("com.chikislist.widget.footer"); ?>