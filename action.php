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

// Custom constants
if (!defined('DOKU_PLUGIN_IMAGES')) define('DOKU_PLUGIN_IMAGES',DOKU_BASE.'lib/plugins/autobackup/images/');
if (!defined('DOKU_DATA')) define('DOKU_DATA', "/var/lib/dokuwiki/data/");
if (!defined('AUTOBACKUP_PLUGIN')) define('AUTOBACKUP_PLUGIN', DOKU_PLUGIN.'autobackup/');

require_once DOKU_PLUGIN.'action.php';
require_once AUTOBACKUP_PLUGIN.'lib/Dropbox.php';

class action_plugin_autobackup extends DokuWiki_Action_Plugin {

    private $user;

    /**
     * Register hooks from dokuwiki
     */
    public function register(Doku_Event_Handler &$controller) {

       $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'handle_action_act_preprocess');
       $controller->register_hook('TPL_CONTENT_DISPLAY', 'BEFORE', $this, 'handle_tpl_content_display');
       $controller->register_hook('TPL_ACT_UNKNOWN', 'BEFORE', $this, 'handle_tpl_act_unknown');
       $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'handle_ajax_call_unknown');   
    }

    public function handle_action_act_preprocess(Doku_Event &$event, $param) {

      $this->_set_user();
      global $JSINFO;
      $JSINFO['user'] = $this->user;

      switch ( $event->data ) {
          case "memories":
            if ( $user == "unknown" ) {
              send_redirect("/doku.php?do=login");
              $event->preventDefault();
              $event->stopPropagation();
              return;
            }
            $event->preventDefault();
            break;
          case "restore":
            if ( $user == "unknown" ) {
              send_redirect("/doku.php?do=login");
              $event->preventDefault();
              $event->stopPropagation();
              return;
            }
            $event->preventDefault();
            break;
          default:
            return;
            break;
        }
    }

    public function handle_ajax_call_unknown(Doku_Event &$event, $param) {
      
      $this->_set_user();

      $event->preventDefault();
      $event->stopPropagation();

      $json = new StdClass;

      switch ( $event->data ) {
        case "dropbox.enable":
          $result = Dropbox::enable_for( $this->user );
          $json->message = ( $result === true )
            ? "You have been queued to have Dropbox installed on your account, you should get an email detailing the next steps within 10 minutes"
            : "$result. Please contact your deployment manager";
          break;
        case "dropbox.disable":
          $json->message = Dropbox::disable_for( $this->user );
          break;
        case "restore.memory":
          $json = $this->_restore_memory();
          break;
        default:
          $json->message = "Unsupported request";
          break;
      }

      echo json_encode($json);
    }

    public function handle_tpl_content_display(Doku_Event &$event, $param) {
    }

    public function handle_tpl_act_unknown(Doku_Event &$event, $param) {

      global $INPUT;

      $this->_set_user();
      
      try {
        switch ( $event->data ) {
          case "memories":
            echo "<h2>Memories</h2>";
            $this->_show_backup_options();
            $this->_show_memories();
            $event->preventDefault();
            break;
          case "restore":
            $this->_do_restore();
            $event->preventDefault();
            break;
          default:
            return;
            break;
        }
      } catch ( Exception $e ) {
        echo $e->getMessage();
      }
    }

    private function _set_user() {

      global $INFO;
      $user = trim($INFO['client']);
      $this->user = filter_var( $user, FILTER_VALIDATE_IP ) 
        ? "unknown" // set the user as unknown if client turns out to be an IP
        : $user ;
    }

    /**
     * Prints out the backup options
     */
    private function _show_backup_options() {

      $dropbox_status = Dropbox::status_for( $this->user );
      $dropbox_button = Dropbox::generate_button( $this->user );

      include AUTOBACKUP_PLUGIN."inc/backup_options.php"; # TODO: not this
    }

    private function _show_memories() {

      $memory_list = "/home/{$this->user}/memories.list";

      $backups = array();

      if ( file_exists($memory_list) )
        $backups = json_decode( file_get_contents( $memory_list ) );

      $current = new StdClass;
      $current->date = "Current";
      $current->source = "Dokuwiki";

      array_unshift( $backups, $current );

      include AUTOBACKUP_PLUGIN."inc/memories.php"; # TODO: not this
    }

    private function _do_restore() {

      $username = $this->user;

      include AUTOBACKUP_PLUGIN."inc/restore.php"; # TODO: not this 
    }

    private function _restore_memory() {

      $username = $this->user;
      $source = $_POST['source'];
      $timestamp = stripslashes(trim($_POST['timestamp']));
      
      $json = new StdClass;

      // Try to extract and link the wiki
      try {

        // get current timestamp
        $cmd = "braincase-wiki-switcher $username";
        exec($cmd, $out, $ret);
        $current_timestamp = trim($out[0]);

        // Check if we need to do a restore
        if ( $current_timestamp != $timestamp
          && !file_exists("/home/$username/.dokuwiki/data.$timestamp") ) {
          
          // Restore the backup requested
          $cmd = "braincase-restore $username $source $timestamp dokuwiki";
          exec($cmd, $out, $ret);
          
          if ( $ret != 0 ) {
            $out = implode("\n", $out);
            throw new Exception("Failed to restore the timestamp.\n$ $cmd\n$out\nReturned $ret");
          }
        }
        
        // Check if we need to do a switch
        if ( $current_timestamp != $timestamp ) {
          // Setup the dokuwiki links
          $cmd = "braincase-wiki-switcher $username $timestamp";
          exec($cmd, $out, $ret);

          if ( $ret != 0 ) {
            $out = implode("\n", $out);
            throw new Exception("Failed to switch timestamps.\n$ $cmd\n$out\nReturned $ret");
          }
        }

        $json->error = 0;
        $json->message = "Successfully restored the Dokuwiki contents from $timestamp";

      } catch ( Exception $e ) {
        $json->error = 1;
        $json->error_output = $e->getMessage();
      }

      return $json;     
    }
}

// vim:ts=4:sw=4:et:
