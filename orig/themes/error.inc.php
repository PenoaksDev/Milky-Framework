<div class="container" style="margin: 10px;">
<!-- PAGE DATA -->
</div>

<script type="text/javascript">
/*<![CDATA[*/
var traceReg = new RegExp("(^|\\s)trace-file(\\s|$)");
var collapsedReg = new RegExp("(^|\\s)collapsed(\\s|$)");

var e = document.getElementsByTagName("div");
for(var j=0,len=e.length;j<len;j++){
	if(traceReg.test(e[j].className)){
		e[j].onclick = function(){
			var trace = this.parentNode.parentNode;
			if(collapsedReg.test(trace.className))
				trace.className = trace.className.replace("collapsed", "expanded");
			else
				trace.className = trace.className.replace("expanded", "collapsed");
		}
	}
}
/*]]>*/
</script>