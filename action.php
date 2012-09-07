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

define('ENABLED_USER_LIST',DOKU_INC.'data/dropbox/enabled_users.txt');
define('ENABLE_USERS_QUEUE',DOKU_INC.'data/dropbox/enable_queue.txt');

require_once DOKU_PLUGIN.'action.php';

class action_plugin_autobackup extends DokuWiki_Action_Plugin {

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
          #$this->_enable_dropbox( $USERINFO['name'] );
          break;
        case "dropbox.disable":
          #$this->_disable_dropbox( $USERINFO['name'] );
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

      if ( !file_exists(ENABLED_USER_LIST) )
        return "disabled";
      
      $enabled_users = file_get_contents(ENABLED_USER_LIST);

      if ( preg_match("/^$user$/", $enabled_users ) )
        return "enabled";

      return "disabled";
    }
}

// vim:ts=4:sw=4:et:
