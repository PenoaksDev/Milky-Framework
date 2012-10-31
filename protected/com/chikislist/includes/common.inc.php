<!-- Stylesheets -->

<link rel="stylesheet" type="text/css" href="%css%/reset.css" />
<!-- <link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Knewave|Yellowtail|Mrs+Saint+Delafield|Delius+Unicase" /> -->
<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Prosto+One" />
<link rel="stylesheet" type="text/css" href="%css%/stylesheet.css" />
<link rel="stylesheet" type="text/css" href="%css%/ui-lightness/jquery-ui-1.8.18.custom.css" />
<link rel="stylesheet" type="text/css" href="%css%/csTransPie-min.css" />
<link rel="stylesheet" type="text/css" href="%css%/themes/default.css" />

<!-- Scripts -->
<script type="text/javascript" src="%js%/iscroll.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script type="text/javascript" src="%js%/jquery.tools.min.js"></script>
<script type="text/javascript" src="%js%/jquery.urlEncode.js"></script>
<script type="text/javascript" src="%js%/cufon-yui.js"></script>
<script type="text/javascript" src="%js%/arial.js"></script>
<script type="text/javascript" src="%js%/cuf_run.js"></script>
<script type="text/javascript" src="%js%/jquery-ui-1.8.16.custom.min.js"></script>
<script type="text/javascript" src="%js%/jquery.form.js"></script>
<script type="text/javascript" src="%js%/jquery.password_meter.js"></script>
<script type="text/javascript" src="%js%/jquery.json2xml.js"></script>
<script type="text/javascript" src="%js%/jquery.combobox.js"></script>
<script type="text/javascript" src="%js%/jquery.popupWindow.js"></script>
<script type="text/javascript" src="%js%/jquery.history.js"></script>
<script type="text/javascript" src="%js%/jquery.wookmark.min.js"></script>
<script type="text/javascript" src="%js%/csTransPie-min.js"></script>
<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?libraries=places&sensor=true"></script>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>

<script type="text/javascript">

function popUp (URL)
{
	day = new Date();
	id = day.getTime();
	eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width=640,height=602,left = 400,top = 149');");
}

$.fn.fadeInImage = function(url){
//	$(document).append("<img id=\"imgLoader\" />");
  var t = this;
  $('<img />')
    .attr('src', url)
    .load(function(){
       t.each(function(){ 
          $(this).css('backgroundImage', 'url('+url+')' );
       });
    });
   return this;
 }

$(document).ready(function() {
	$("ul.dropdown").parent().addClass("gumdrop");
	
	$("#menu ul li.gumdrop").hover(function()
	{
		$(this).find("ul.dropdown").fadeIn(100);
        }, function(){
        	$(this).find("ul.dropdown").fadeOut(100);
	});
	
	$("#menu ul li.gumdrop ul li a").click(function()
	{
		$(this).parent().parent().parent().find("ul.dropdown").fadeOut(100);
	});
	
	$("img#wallpaper").load(function () {$(this).fadeIn(300)});
});

</script>

<!-- GOOGLE Analytics -->
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-30276904-1']);
  _gaq.push(['_setDomainName', 'chikislist.com']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>