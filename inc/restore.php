<?php

$timestamp = $_GET["timestamp"];
$source = $_GET["source"];
#Clears Cache
$clear = exec("touch /etc/dokuwiki/local.php"); 
?>

<h1>Remember</h1>

<div style="text-align: center;" >
	<p style="font-size: large;">Restoring <span id="timestamp"><?php echo $timestamp; ?></span> from <span id="source"><?php echo $source; ?></span>... <span id="restore-result"></span></p>
	<p id="restore-message" style="display: none;"><span class="message"></span> - <a href="<?php echo DOKU_URL;?>/doku.php?do=memories">click here</a> to return to your memories</p>
	<div id="loading-gif-div">
		<img src="<?php echo DOKU_PLUGIN_IMAGES; ?>/loading.gif" alt="restoring..."/>
	</div>
	<pre style="margin: 0px auto; display: none; text-align: left; width: 70%; background: #ddd;" id="error-output"></pre>
</div>
