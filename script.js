jQuery(document).ready(function() {

	jQuery("#Disable_dropbox").click(function() {
		jQuery.post(
			DOKU_BASE+"lib/exe/ajax.php",
			{ call: 'dropbox.disable' },
			function(j) {
				alert(j.message);
			},
			'json'
		);
	});

	jQuery("#Enable_dropbox").click(function() {
		jQuery.post(
			DOKU_BASE+"lib/exe/ajax.php",
			{ call: 'dropbox.enable' },
			function(j) {
				alert(j.message);
			},
			'json'
		);
	});
});