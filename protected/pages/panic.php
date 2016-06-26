<?
	$err = $GLOBALS["lasterr"];
	@$stackTrace = $GLOBALS["stackTrace"];
	
	if (is_null($err))
		die("Chiori Framework encountered a problem presenting the error page. Contact support.");
	
	$signature = date("Y-m-d h:i:m") . " | " . getFramework()->getProduct() . "/" . getFramework()->getVersion() . " | " . apache_get_version();
	
	function seekNext($handle)
	{
		$offset = ftell($handle);
		while(fgetc($handle) != chr(10) && !feof($handle))
		{
			fseek($handle, $offset++);
		}
	}
	
	function codeSamp($file, $line)
	{
		if (!empty($file))
		{
			$handle = fopen($file, "r");
			if ($handle)
			{
				if ($line > 5)
				{
					for ($x=1;$x<$line - 5;$x++) seekNext($handle);
					$dl = 5;
				}
				else
				{
					$dl = $line;
				}
				
				for ($x=0;$x<5+$dl;$x++)
				{
					$cline = fgets($handle);
					
					if (!empty($cline))
					{
						if ($x == $dl)
						{
							echo ("<span class=\"error\"><span class=\"ln error-ln\">" . ($line + $x) . "</span> " . htmlentities($cline) . "</span>");
						}
						else
						{
							echo ("<span class=\"ln\">" . ($line + $x) . "</span> " . htmlentities($cline));
						}
					}
				}
				
				fclose($handle);
			}
		}		
	}
?>

	<h1>Chiori Framework - Panic Attack</h1>
	
	<p class="message">
		<? echo($err->getMessage() . " in " . $err->getFile() . " on line " . $err->getLine()); ?>
	</p>

	<div class="source">
		<p class="file"><? echo($err->getFile() . "(" . $err->getLine() . ")"); ?></p>

<div class="code">
	<pre><? codeSamp($err->getFile(), $err->getLine()); ?></pre>
</div>
</div>

<div class="traces">
	<h2>Stack Trace</h2>
	<table style="width:100%;">
		<?
			if (is_null($stackTrace))
				$result = $err->getTrace();
			
			$l = 0;
			
			foreach ($result as $trc)
			{
				?>
				<tr class="trace <? echo (substr($trc["file"], 0, strlen($chiori->fwRoot)) == $chiori->fwRoot) ? "core" : "app"; ?> collapsed">
					<td class="number">#<? echo $l; ?></td>
					<td class="content">
						<div class="trace-file">
							<div class="plus">+</div>
							<div class="minus">–</div>
							&nbsp;<? echo($trc["file"] . "(" . $trc["line"] . "): <strong>" . $trc["class"] . $trc["type"] . $trc["function"] . "</strong>"); ?>
						</div>
						<div class="code">
							<pre><? codeSamp($trc["file"], $trc["line"]); ?></pre>
						</div>
					</td>
				</tr>
				<?
				$l++;
			}
		?>
	</table>
</div>

<div class="version"><? echo($signature); ?></div>