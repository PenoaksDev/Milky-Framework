<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="%css%/default.css" rel="stylesheet" type="text/css" />
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js"></script>
<script type="text/javascript" src="%js%/cufon-yui.js"></script>
<script type="text/javascript" src="%js%/arial.js"></script>
<script type="text/javascript" src="%js%/cuf_run.js"></script>
<script type="text/javascript" src="%js%/radius.js"></script>

<script>
var dragapproved=false
var minrestore=0
var initialwidth,initialheight
var ie5=document.all&&document.getElementById
var ns6=document.getElementById&&!document.all

function iecompattest(){
return (!window.opera && document.compatMode && document.compatMode!="BackCompat")? document.documentElement : document.body
}

function drag_drop(e){
if (ie5&&dragapproved&&event.button==1){
document.getElementById("dwindow").style.left=tempx+event.clientX-offsetx+"px"
document.getElementById("dwindow").style.top=tempy+event.clientY-offsety+"px"
}
else if (ns6&&dragapproved){
document.getElementById("dwindow").style.left=tempx+e.clientX-offsetx+"px"
document.getElementById("dwindow").style.top=tempy+e.clientY-offsety+"px"
}
}

function initializedrag(e){
offsetx=ie5? event.clientX : e.clientX
offsety=ie5? event.clientY : e.clientY
document.getElementById("dwindowcontent").style.display="none" //extra
tempx=parseInt(document.getElementById("dwindow").style.left)
tempy=parseInt(document.getElementById("dwindow").style.top)

dragapproved=true
document.getElementById("dwindow").onmousemove=drag_drop
}

function loadwindow(url,width,height){
if (!ie5&&!ns6)
window.open(url,"","width=width,height=height,scrollbars=1")
else{
document.getElementById("dwindow").style.display=''
document.getElementById("dwindow").style.width=initialwidth=width+"px"
document.getElementById("dwindow").style.height=initialheight=height+"px"
document.getElementById("dwindow").style.left=(document.body.offsetWidth*.5)-(width*.5)
document.getElementById("dwindow").style.top=(document.body.offsetHeight*.5)-(height*.5)
document.getElementById("dwindow").style.top="5px"
document.getElementById("cframe").src=url
}
}

function maximize(){
if (minrestore==0){
minrestore=1 //maximize window
document.getElementById("maxname").setAttribute("src","restore.gif")
document.getElementById("dwindow").style.width=ns6? window.innerWidth-20+"px" : iecompattest().clientWidth+"px"
document.getElementById("dwindow").style.height=ns6? window.innerHeight-20+"px" : iecompattest().clientHeight+"px"
}
else{
minrestore=0 //restore window
document.getElementById("maxname").setAttribute("src","max.gif")
document.getElementById("dwindow").style.width=initialwidth
document.getElementById("dwindow").style.height=initialheight
}
document.getElementById("dwindow").style.left=ns6? window.pageXOffset+"px" : iecompattest().scrollLeft+"px"
document.getElementById("dwindow").style.top=ns6? window.pageYOffset+"px" : iecompattest().scrollTop+"px"
}

function closeit(){
document.getElementById("dwindow").style.display="none"
}

function stopdrag(){
dragapproved=false;
document.getElementById("dwindow").onmousemove=null;
document.getElementById("dwindowcontent").style.display="" //extra
}

</script>