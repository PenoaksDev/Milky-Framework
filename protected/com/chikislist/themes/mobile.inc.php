<?
	$location = $chiori->nameSpaceGet("com.chikislist.modules.api")->rewardsLoad();
?>

<div id="mobile-wrapper">
	<!-- PAGE DATA -->
</div>

<div id="mobile-ft" style="height: 20px;">
	<div id="legal">
		<p class="clearfix" style="font-size: 9px;">
			<span style="float: left;"><b>Copyright &copy; <?PHP echo(date("Y")); ?> Chiki's List (Chiori Greene) (chikislist.com)</b></span>
			<span style="float: right; text-align: right;"><b>Powered by Chiori Framework Version <? echo $chiori->Version(); ?>.</b></span>
		</p>
	</div>
</div>

<div id="warningDialog"></div>
<div id="loadingScreen"></div>
<div id="blinders" style="display: none;">
	<div>
		<p>Please Wait...</p>
		<img src="http://images.chikislist.com/sprite/ajax-loader.gif" />
	</div>
</div>

<script type="text/javascript">
	$("#loadingScreen").dialog({
		autoOpen: false,	// set this to false so we can manually open it
		dialogClass: "loadingScreenWindow",
		closeOnEscape: false,
		draggable: false,
		show: "fade",
		width: 460,
		minHeight: 50,
		modal: true,
		buttons: {},
		resizable: false
	}); // end of dialog
	
	$("#warningDialog").dialog({
		autoOpen: false,
		closeOnEscape: false,
		draggable: false,
		show: "fade",
		width: 460,
		minHeight: 50,
		modal: true,
		resizable: false,
		buttons: {
			//text: "Ok", click: function () { $(this).dialog("close"); }
		}
	});

	function waitingDialog(func)
	{
		//$("#blinders").show("slide", { direction: "left" }, 800);
		if (typeof(func) == "function")
		{
			$("#blinders").fadeIn(600, func);
		}
		else
		{
			$("#blinders").fadeIn(600);
		}
	}
	
	function closeWaitingDialog()
	{
		//$("#blinders").hide("slide", { direction: "right" }, 800);
		$("#blinders").fadeOut(800);
	}
	
	waitingDialog();
	
	function showWarning(caption)
	{
		var icon = '<span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 50px 0;"></span>';
		$("#warningDialog").html(caption && '' != caption ? icon + caption : icon + 'An error occured. Try again.');
		$("#warningDialog").dialog('open');
		
//		alert(caption && "" != caption ? icon + caption : icon + "An error occured. Try again.");
	}
	
	function loadNew(page, self)
	{
		page = page.replace("#", "");
		
		if (page.substring(0, 4) != "http")
		page = "http://m.chikislist.com/ajax/" + page;
		
		if (self == null)
		var self = $("#mobile-wrapper");
		
		waitingDialog(function () {
			self.load(page, function () {
				closeWaitingDialog();
			});
		});
		
		/*
		self.fadeOut(800, function () {
			self.load(page, function () {
				self.fadeIn(800);
				closeWaitingDialog();
			})
		});
		*/
	}
	
	function updateHTML(html, self)
	{
		waitingDialog();
		
		if (self == null)
		var self = $("#mobile-wrapper");
		
		self.fadeOut(800, function () {
			self.html(html).fadeIn(800, function () {closeWaitingDialog();});
		});
	}
	
	function newImage(src, self)
	{
		if (self == null) self = $("#slide_window");
		
		self.fadeOut(function() { 
			$(this).attr("src", ""); 
			$(this).load(function() { $(this).fadeIn(); }); 
			$(this).attr("src", src); 
		}); 
	}
	
	$(window).bind("hashchange", function(e) {
		loadNew(window.location.hash);
	});
	
	$(document).ready(function () {
		loadNew(window.location.hash);
	});
	
	setTimeout(function () {
		window.location.reload();
	}, 3600000);
	
	//setAwayTimeout(180000);
	//$(document).onAway(function (isIdle, isAway) {
	//	loadNew("");
	//});
</script>