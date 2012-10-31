<?php

/**
 * A static class that will add users to dropbox queues
 * for a cron job to read and act upon
 */
class Dropbox {
	
	public static function enable_for( $user ) {

		exec("braincase-dropbox queue $user", $nil, $enable_retval);
		exec("braincase-dropbox status $user", $out, $status_retval);
		
		$status = trim($out[0]);

		if ( $status == "queued" )
			return true;

		if ( $enable_retval != 0 )
			return "Something went wrong trying to queue $user for Dropbox install";

		if ( $status_retval != 0 )
			return "Something went wrong trying to check the Dropbox status of $user";

		return "An unknown error occured, the status of the user was $status";
	}

	public static function disable_for( $user ) {
		

	}

	public static function status_for( $user ) {

		exec("braincase-dropbox status $user", $out, $ret);

		if ( $ret != 0 )
			return "unknown";

		return trim($out[0]);
	}

	public static function generate_button( $user ) {

		$status = self::status_for( $user );

		$status_button = '<input type="submit" value="{{value}}" class="button" id="{{id}}"{{disabled}}/>';
		$value = "???";
		$disabled = "";
		$id = "_dropbox";

		switch ( $status ) {
		case "disabled":
			$value = "Enable Dropbox" ;
			$id = "Enable$id";
			break;
		case "enabled":
			$value = "Disable Dropbox";  
			$id = "Disable$id";
			break;
		case "queued":
			$value = "Queued";
			$disabled = " disabled";
			break;
		default:
			break;
		}

		return str_replace( array(
			"{{value}}",
			"{{id}}",
			"{{disabled}}"
		), array(
			$value,
			$id,
			$disabled
		), $status_button);
	}
}