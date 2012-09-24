<div style="background: #c0c0c0; border-radius: 10px;">
	<p onclick="javascript:jQuery('#backup-options-slider').slideToggle();" style="cursor: pointer; padding: 10px; font-weight: bold; margin:0px;">Backup Options</p>
	<div id="backup-options-slider" style="display: none; padding: 10px; ">
		<p>Dropbox Backups: <strong><?php echo $status;?></strong> <?php echo $status_button;?></p>
	</div>
</div>