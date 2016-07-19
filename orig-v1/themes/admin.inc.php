<div id="loadingScreen"></div>
<div id="warningDialog" title="Warning"></div>
<div id="userDialog" title="Select Dialog"></div>

<script type="text/javascript">
	// create the loading window and set autoOpen to false
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
		resizable: false,
		open: function() {
			// scrollbar fix for IE
			$('body').css('overflow','hidden');
		},
		close: function() {
			// reset overflow
			$('body').css('overflow','auto');
		}
	}); // end of dialog
	
	$("#warningDialog").dialog({
		autoOpen: false,
		draggable: false,
		resizable: false,
		show: "fade",
		modal: true,
		buttons: {
			Ok: function() {
				$( this ).dialog( "close" );
			}
		}
	});
	
	$("#userDialog").dialog({
		autoOpen: false,
		draggable: false,
		resizable: false,
		width: 500,
		show: "fade",
		modal: true,
		buttons: {
			Cancel: function() {
				$( this ).dialog( "close" );
			}
		}
	});
	
	function selectDialog(input)
	{
		$.post("/api/getdialog.php", input, function (data) {
			$("#userDialog a").unbind();
			$("#userDialog").html(data).dialog("option", "title", input.title && '' != input.title ? input.title : 'Select Dialog').dialog("open");
			$("#userDialog a#lstitem").click(function () {
				$("#userDialog").dialog('close');
				input.onclick($(this).attr("rel"), $(this).html());
			});
		});
	}
	
	function waitingDialog(waiting)
	{
		$("div.tooltip").hide();
		$("#loadingScreen").html(waiting.message && '' != waiting.message ? waiting.message : 'Please wait...');
		$("#loadingScreen").dialog('option', 'title', waiting.title && '' != waiting.title ? waiting.title : 'Loading');
		$("#loadingScreen").dialog('open');
	}
	
	function closeWaitingDialog()
	{
		$("#loadingScreen").dialog('close');
		$("p.warning, p.success").fadeIn(500);
	}
	
	function showWarning(caption)
	{
		var icon = '<span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 50px 0;"></span>';
		$("#warningDialog").html(caption && '' != caption ? icon + caption : icon + 'An error occured. Try again.');
		$("#warningDialog").dialog('open');
	}
	
	function showDialog(caption)
	{
		var icon = '<span class="ui-icon ui-icon-circle-check" style="float:left; margin:0 7px 50px 0;"></span>';
		$("#warningDialog").html(caption && '' != caption ? icon + caption : icon + 'Opperation Successful.');
		$("#warningDialog").dialog('open');
	}
	
	waitingDialog({});

	function goto_frame(url)
	{
//		console.log("Loading AJAX Page: http://panel.chioricloud.com/" + url);
		$("div.tooltip").hide();
		
		var self = $('#ajaxframe');
		
		self.fadeOut(300, function () {
			self.load("http://panel.chioricloud.com/" + url, function () {
				self.fadeIn(300);
			});
		});
		
		try
		{
			history.pushState({}, "", url);
		}
		catch(err)
		{
		
		}
	}
	
	function goto_frame_post(url, xml) //old
	{
		$("div.tooltip").hide();
		$('#ajaxframe').fadeOut(300, function () {
			$.ajax({
				url: "http://panel.chioricloud.com/" + url,
				type: "post",
				data: {xml: escape(xml)},
				success:
					function (data) {
						$('#ajaxframe').fadeIn(300);
						$('#ajaxframe').html(data);
					},
				error:
					function (err) {
						$('#ajaxframe').fadeIn(300);
						$('#ajaxframe').html(err['responseText']);
						alert("AJAX Request Failed. Notify Administrators.");
					}
			});
		});
	}
	
	function setHTML(data)
	{
		$("div.tooltip").hide();
		$('#ajaxframe').fadeOut(300, function () {
			$('#ajaxframe').fadeIn(300);
			$('#ajaxframe').html(data);
		});
	}
	
	function getHTML(url, blind, func)
	{
		// console.log("Loading AJAX Page: http://www.chioricloud.com/dashboard/" + url);
		$("div.tooltip").hide();
		if (blind) waitingDialog({});
		$.get("http://<? echo($_SERVER["SERVER_NAME"]); ?>/" + url, function (data) {
			if (blind) closeWaitingDialog();
			func(data);
		}).error(function (err) {
			if (blind) closeWaitingDialog();
			func(err['responseText']);
			alert("AJAX Request Failed. Notify Administrator.");
			// console.log("AJAX Load Failed.");
		});
	}
	
	$("div#menu").hide();
	$("div#body").hide();
	$("div#footer").hide();
	
	$(document).ready(function()
	{
		closeWaitingDialog()
		
		$("#select_userlist a[title]").tooltip({
			position: "top center",
			offset: [-15, 13],
			effect: "fade",
			opacity: 0.9
		});
		
		$("div#menu").fadeIn(1500);
		$("div#body").fadeIn(1000);
		$("div#footer").fadeIn(1500);
		
		<?
			$opt = $chiori->GetSetting("USER_HOMEPAGE", "", true);
			if (!empty($opt))
			{
				echo("goto_frame(\"" . $opt . "\");");
			}
		?>
	});
</script>

<!-- HEADER_ADMIN -->

<div id="wrapper" class="div01">
	<div id="buffer"></div>
	<div id="body">
		<div id="whole">
			<div id="ajaxframe">
				<!-- PAGE DATA -->
			</div>
			<!-- INFO -->
		</div>
		<div class="clearfix"></div>
	</div>
	<div id="push"></div>
</div>

<!-- FOOTER -->

<img id="wallpaper" src="http://images.chioricloud.com/bg/bgroller.php"></div>