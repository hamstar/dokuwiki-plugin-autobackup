<?php

/**
 * A static class that will add users to dropbox queues
 * for a cron job to read and act upon
 */
class Dropbox {
	
	public static function enable_for( $user ) {

		global $conf;

		$saved = self::_add_to_queue( 
			$user,
			$conf["autobackup"]["dropbox_enable_queue"]
		);

		return ($saved) 
		  ? "Dropbox is queued to be enabled on your account.  You will receive an email soon with further instructions."
		  : "Something went wrong, please contact your deployment manager";
	}

	public static function disable_for( $user ) {
		
		global $conf;

		$saved = self::_add_to_queue( 
			$user,
			$conf["autobackup"]["dropbox_disable_queue"]
		);

		return ($saved)
		  ? "Dropbox is queued to be disabled for your account."
		  : "Something went wrong, please contact your deployment manager";
	}

	private static function _add_to_queue( $user, $q ) {
		
		$fn = self::_build_filename( $q );

		if ( !file_exists( $fn ) )
			return false;

		return ( file_put_contents( $fn, "$user\n", FILE_APPEND ) !== FALSE )
		  ? true
		  : false;
	}

	private static function _build_filename( $page_id ) {

		return DOKU_INC."data/pages/".str_replace( ":", "/", $page_id );
	}
}