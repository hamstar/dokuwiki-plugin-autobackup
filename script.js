autobackup = {
	
	hide_title: function() {
		jQuery("div.page h1.title").remove();
	},

	add_memories_button: function() {
		li = jQuery("<li/>").append( 
			jQuery("<a/>")
			.addClass("action")
			.attr("href", "/doku.php?id=test:memories&do=memories")
			.text("Memories")
		);

		jQuery("div#dokuwiki__usertools ul").prepend( li );
	}
};

jQuery(document).ready(function() {

	// Modify the page
	autobackup.hide_title();
	autobackup.add_memories_button();

	jQuery("#Disable_dropbox").click(function() {
		jQuery.post(
			DOKU_BASE+"lib/exe/ajax.php",
			{ call: 'dropbox.disable' },
			function(j) {
				alert(j.message);
				location.reload();
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
				location.reload();
			},
			'json'
		);
	});
});