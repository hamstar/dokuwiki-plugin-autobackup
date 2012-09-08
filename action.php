<?php
/**
 * DokuWiki Plugin autobackup (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Robert McLeod <hamstar@telescum.co.nz>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
if (!defined('AUTOBACKUP_PLUGIN')) define('AUTOBACKUP_PLUGIN', DOKU_PLUGIN.'autobackup/');

require_once DOKU_PLUGIN.'action.php';

class action_plugin_autobackup extends DokuWiki_Action_Plugin {

    private $dropbox_enabled_users;
    private $dropbox_enable_queue;
    private $dropbox_disable_queue;
    private $restore_queue;

    public function __construct() {

      $this->dropbox_enabled_users = DOKU_INC.'data/braincase/dropbox/enabled_users.txt';
      $this->dropbox_enable_queue = DOKU_INC.'data/braincase/dropbox/enable_queue.txt';
      $this->dropbox_disable_queue = DOKU_INC.'data/braincase/dropbox/disable_queue.txt';
      $this->restore_queue = DOKU_INC."data/pages/braincase/backup/restore_queue.txt";
    }

    public function register(Doku_Event_Handler &$controller) {

       $controller->register_hook('ACTION_ACT_PREPROCESS', 'FIXME', $this, 'handle_action_act_preprocess');
       $controller->register_hook('TPL_CONTENT_DISPLAY', 'BEFORE', $this, 'handle_tpl_content_display');
       $controller->register_hook('TPL_ACT_UNKNOWN', 'FIXME', $this, 'handle_tpl_act_unknown');
       $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'handle_ajax_call_unknown');   
    }

    public function handle_action_act_preprocess(Doku_Event &$event, $param) {
    }

    public function handle_ajax_call_unknown(Doku_Event &$event, $param) {

      global $USERINFO;

      switch ( $event->data ) {
        case "dropbox.enable":
          file_put_contents($this->dropbox_enable_queue, $USERINFO["name"], FILE_APPEND);
          echo json_encode(array("message" => "Dropbox is queued to be enabled on your account.  You will receive an email soon with further instructions."));
          break;
        case "dropbox.disable":
          file_put_contents($this->dropbox_enable_queue, $USERINFO["name"], FILE_APPEND);
          echo json_encode(array("message" => "Dropbox is queued to be disabled for your account."));
          break;
        default:
          return;
          break;
      }

      $this->preventDefault();
    }

    public function handle_tpl_content_display(Doku_Event &$event, $param) {
      
      global $ACT;

      if ( $ACT == "profile" )
        $this->_add_backup_section( $event );
    }

    public function handle_tpl_act_unknown(Doku_Event &$event, $param) {

      try {
        switch ( $event->data ) {
          case "restore.backup":
            $this->_restore_backup();
            break;
          default:
            return;
            break;
        }
      } catch ( Exception $e ) {
        echo $e->getMessage();
      }
    }

    private function _restore_backup() {
      
      # save the zip file
      $filename = $this->_clean_filename( $_FILES["restore-file"]["name"] );
      $tmp_file = $_FILES["restore-file"]["tmp_name"];
      $new_file = AUTOBACKUP_PLUGIN."restore/zipped/$filename";
      move_uploaded_file($tmp_file, $new_file);

      # determine the folder to unzip to and make it
      $extract_to = AUTOBACKUP_PLUGIN."restore/unzipped/".substr( $filename, 0, -3 );
      mkdir($extract_to);

      # unzip the files
      `unzip $new_file -b $extract_to 2>&1`; #TODO: inspect for errors here

      if ( !file_exists("$extract_to/wiki.tar.gz") )
        throw new Exception("Wiki data missing from the restore file");

      # untar the wiki data
      `tar -xzf $extract_to/wiki.tar.gz 2>&1`; #TODO: inspect for errors here

      if ( !file_exists("$extract_to/data") )
        throw new Exception("Wiki data did not extract from the archive");

      # Remove Braincase system data
      rmdir("$extract_to/data/pages/braincase");
      rmdir("$extract_to/data/meta/braincase");

      # copy the dokuwiki stuff across
      $copy_cmd = "cp -Rf $extract_to/data ".DOKU_INC." 2>&1";
      exec( $copy_cmd, $out, $ret);

      if ( $ret != 0 )
        echo implode( "\n", $out );

      # notify restore cron to copy other data we don't have perms to
      file_put_contents( $this->restore_queue, "$extract_to", FILE_APPEND )
    }

    private function _clean_filename( $name ) {

      if ( substr( $name, -3) != "zip" )
        throw new Exception("Restore file must be a zip archive.");

      $name = stripslashes($name);
    }

    private function _add_backup_section( &$event ) {

      global $USERINFO;

      $form = file_get_contents(AUTOBACKUP_PLUGIN."form.html");
      $form = $this->_add_dropbox_status_to_form( $form );

      $event->data .= $form;
    }

    private function _add_dropbox_status_to_form( $form ) {

      $status = $this->_get_dropbox_status( $USERINFO['name'] );
      $status_action = ( $status == "disabled" ) ? "Disable" : "Enable";

      return str_replace(array(
        "{{status}}",
        "{{status_action}}"
      ), array(
        $status,
        $status_action
      ), $form);
    }

    private function _get_dropbox_status( $user ) {

      if ( !file_exists($this->dropbox_enabled_users) )
        return "disabled";
      
      $enabled_users = file_get_contents($this->dropbox_enabled_users);

      if ( preg_match("/^$user$/", $enabled_users ) )
        return "enabled";

      return "disabled";
    }
}

// vim:ts=4:sw=4:et:
