var autobackup = {

	init: function () {

		this.hide_title();
		this.add_memories_button();
		this.activate_remember_button();
		this.activate_disable_dropbox_button();
		this.activate_enable_dropbox_button();
	},

	hide_title: function () {
		jQuery("div.page h1.title").remove();
	},

	add_memories_button: function () {
		var li = jQuery("<li/>").append( 
			jQuery("<a/>")
				.addClass("action")
				.attr("href", "/doku.php?id=test:memories&do=memories")
				.text("Memories")
		);

		jQuery("div#dokuwiki__usertools ul").prepend(li);
	},

	activate_remember_button: function () {
		jQuery("#apply-backup").click(function () {
			var name = jQuery("input[name='backup-selection']:checked").parents('tr').text().trim().replace(/\s+/, ' ');
			alert("TODO: apply the backup " + name);
		});
	},

	activate_disable_dropbox_button: function () {
		jQuery("#Disable_dropbox").click(function () {
			jQuery.post(
				DOKU_BASE + "lib/exe/ajax.php",
				{ call: 'dropbox.disable' },
				function (j) {
					alert(j.message);
					location.reload();
				},
				'json'
			);
		});
	},

	activate_enable_dropbox_button: function () {
		jQuery("#Enable_dropbox").click(function () {
			jQuery.post(
				DOKU_BASE + "lib/exe/ajax.php",
				{ call: 'dropbox.enable' },
				function (j) {
					alert(j.message);
					location.reload();
				},
				'json'
			);
		});
	}
};

jQuery(document).ready(function () {

	// Modify the page
	if ( location.href.match(/do=memories/) != null ) {
		autobackup.init();
	}
});