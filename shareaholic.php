<?php
/**
 * @package shareaholic
 * @version 7
 */

/*
Plugin Name: Shareaholic | share buttons, analytics, related content
Plugin URI: https://shareaholic.com/publishers/
Description: Whether you want to get people sharing, grow your fans, make money, or know who's reading your content, Shareaholic will help you get it done. See <a href="admin.php?page=shareaholic-settings">configuration panel</a> for more settings.
Version: 7.0.0.4
Author: Shareaholic
Author URI: https://shareaholic.com
Credits & Thanks: https://shareaholic.com/tools/wordpress/credits
*/


/**
* if we ever wanted to disable warning notices, use the following:
* error_reporting(E_ALL ^ E_NOTICE);
*/

define('SHAREAHOLIC_DIR', dirname(__FILE__));
define('SHAREAHOLIC_ASSET_DIR', get_option('siteurl') . '/wp-content/plugins/' . plugin_basename(dirname(__FILE__)) . '/assets/');
load_plugin_textdomain('shareaholic', false, basename(dirname(__FILE__)) . '/languages/');

// because define can use function returns and const can't
define('SHAREAHOLIC_DEBUG', getenv('SHAREAHOLIC_DEBUG'));

require_once(SHAREAHOLIC_DIR . '/utilities.php');
require_once(SHAREAHOLIC_DIR . '/global_functions.php');
require_once(SHAREAHOLIC_DIR . '/admin.php');
require_once(SHAREAHOLIC_DIR . '/public.php');
require_once(SHAREAHOLIC_DIR . '/notifier.php');
require_once(SHAREAHOLIC_DIR . '/deprecation.php');

/**
 * The main / base class.
 */
class Shareaholic {
  const URL = 'https://shareaholic.com';
  const VERSION = '7.0.0.4';
  private static $instance = false;

  /**
   * The constructor registers all the wordpress actions.
   */
  private function __construct() {
    add_action('wp_ajax_shareaholic_accept_terms_of_service', array('ShareaholicUtilities', 'accept_terms_of_service'));

    add_action('the_content',     array('ShareaholicPublic', 'draw_canvases'));
    add_action('wp_head',         array('ShareaholicPublic', 'wp_head'));
    add_shortcode('shareaholic',  array('ShareaholicPublic', 'shortcode'));

    add_action('wp_ajax_shareaholic_add_location',  array('ShareaholicAdmin', 'add_location'));
    add_action('add_meta_boxes',                    array('ShareaholicAdmin', 'add_meta_boxes'));
    add_action('save_post',                         array('ShareaholicAdmin', 'save_post'));
    add_action('admin_head',                        array('ShareaholicAdmin', 'admin_head'));
    add_action('admin_menu',                        array('ShareaholicAdmin', 'admin_menu'));

    add_action('publish_post', array('ShareaholicNotifier', 'post_notify'));
    add_action('publish_page', array('ShareaholicNotifier', 'post_notify'));

    register_activation_hook(__FILE__, array($this, 'after_activation' ));
    register_deactivation_hook( __FILE__, array($this, 'deactivate' ));
    register_uninstall_hook(__FILE__, array('Shareaholic', 'uninstall' ));
    
    add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), 'ShareaholicUtilities::admin_plugin_action_links', -10);
  }

  /**
   * We want this to be a singleton, so return the one instance
   * if already instantiated.
   *
   * @return Shareaholic
   */
  public static function get_instance() {
    if ( ! self::$instance ) {
      self::$instance = new self();
    }
    self::init();
    return self::$instance;
  }

  /**
   * This function initializes the plugin so that everything is scoped
   * under the class and no varialbes leak outside.
   */
  public static function init() {
    self::update();
    if (ShareaholicUtilities::has_accepted_terms_of_service()) {
      ShareaholicUtilities::get_or_create_api_key();
    }
  }

  /**
   * Runs any update code if the version is different from what's
   * stored in the settings.
   */
  public static function update() {
    if (!ShareaholicUtilities::has_accepted_terms_of_service()) {
      add_action('admin_notices', array('ShareaholicAdmin', 'show_terms_of_service'));
    } else {
      if (ShareaholicUtilities::get_version() != self::VERSION) {
        ShareaholicUtilities::log_event("Upgrade", array ('previous_plugin_version' => ShareaholicUtilities::get_version()));
        ShareaholicUtilities::perform_update();
        ShareaholicUtilities::set_version(self::VERSION);
      }
    }
  }

  public function terms_of_service() {
    if (!ShareaholicUtilities::has_accepted_terms_of_service()) {
      add_action('admin_notices', array('ShareaholicAdmin', 'show_terms_of_service'));
    } else {
      ShareaholicUtilities::get_or_create_api_key();
    }
  }

  /**
   * This function fires after the plugin has been activated.
   */
  public function after_activation() {
    $this->terms_of_service();
    ShareaholicUtilities::log_event("Activate");
    ShareaholicUtilities::recommendations_status_check();

    if (!ShareaholicUtilities::get_version()) {
      ShareaholicUtilities::log_event("Install_Fresh");
    }
  }

  /**
   * This function fires when the plugin is deactivated.
   */
  public function deactivate() {
    ShareaholicUtilities::log_event("Deactivate");
  }

  /**
   * This function fires when the plugin is uninstalled.
   */
  public function uninstall() {
    ShareaholicUtilities::log_event("Uninstall");
    delete_option('shareaholic_settings');
  }
}

// the magic
$shareaholic = Shareaholic::get_instance();
