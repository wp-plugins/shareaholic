<?php
/**
 * Holds the ShareaholicUtilities class.
 *
 * @package shareaholic
 */

require_once(SHAREAHOLIC_DIR . '/curl.php');
require_once(SHAREAHOLIC_DIR . '/six_to_seven.php');

/**
 * This class is just a holder for general functions that have
 * no better place to be.
 *
 * @package shareaholic
 */
class ShareaholicUtilities {
  /**
   * Logs to the PHP error log if plugin's url is set to
   * spreadaholic or the SHAREAHOLIC_DEBUG constant is true.
   *
   * @param mixed $thing anything to be logged, it will be passed to `print_r`
   */
  public static function log($thing) {
    if (preg_match('/spreadaholic/', Shareaholic::URL) || SHAREAHOLIC_DEBUG) {
      error_log(print_r($thing, true));
    }
  }

  /**
   * Locate and require a template, and extract some variables
   * to be used in that template.
   *
   * @param string $template  the name of the template
   * @param array  $vars      any variables to be extracted into the template
   */
  public static function load_template($template, $vars = array()){
    // you cannot let locate_template to load your template
    // because WP devs made sure you can't pass
    // variables to your template :(

    $template_path = 'templates/' . $template . '.php';

    // load it
    extract($vars);
    require $template_path;
  }

  /**
   * Just a wrapper around get_option to
   * get the shareaholic settings. If the settings
   * have not been set it will return an array of defaults.
   *
   * @return array
   */
  public static function get_settings() {
    return get_option('shareaholic_settings', self::defaults());
  }

  /**
   * Destroys all settings except the acceptance
   * of the terms of service.
   *
   * @return bool
   */
  public static function destroy_settings() {
    return delete_option('shareaholic_settings');
  }

  /**
   * Returns the defaults we want becuase PHP does not allow
   * arrays in class constants.
   *
   * @return array
   */
  private static function defaults() {
    return array(
      'disable_tracking' => 'off',
      'api_key' => '',
      'verification_key' => '',
    );
  }

  /**
   * Returns links to add to the plugin options admin page
   *
   * @param  array $links
   * @return array
   */
  public static function admin_plugin_action_links($links) {
  	$links[] = '<a href="admin.php?page=shareaholic-settings">'.__('Settings', 'shareaholic').'</a>';
  	return $links;
  }

  /**
   * Returns whether the user has accepted our terms of service.
   *
   * @return bool
   */
  public static function has_accepted_terms_of_service() {
    return get_option('shareaholic_has_accepted_tos');
  }

  /**
   * Accepts the terms of service.
   */
  public static function accept_terms_of_service() {
    update_option('shareaholic_has_accepted_tos', true);

    ShareaholicUtilities::log_event("AcceptedToS");

    echo "{}";

    die();
  }

  /**
   * Wrapper for wordpress's get_option
   *
   * @param string $option
   *
   * @return mixed
   */
  public static function get_option($option) {
    $settings = self::get_settings();
    return (isset($settings[$option]) ? $settings[$option] : array());
  }

  /**
   * Wrapper for wordpress's update_option
   *
   * @param  array $array an array of options to update
   * @return bool
   */
  public static function update_options($array) {
    $old_settings = self::get_settings();

    $new_settings = self::array_merge_recursive_distinct($old_settings, $array);

    update_option('shareaholic_settings', $new_settings);
  }

  /**
   * Return the current version.
   *
   * @return string that looks like a number
   */
  public static function get_version() {
    return self::get_option('version') ? self::get_option('version') : get_option('SHRSBvNUM');
  }

  /**
   * Return host domain of WordPress install
   *
   * @return string
   */
  public static function get_host() {
    $parse = parse_url(get_bloginfo('url'));
    return $parse['host'];
  }

  /**
   * Set the current version, how simple.
   *
   * @param string $version the version you want to set
   */
  public static function set_version($version) {
    self::update_options(array('version' => $version));
  }

  /**
   * Determines if the first argument version is less than the second
   * argument version. A version can be up four levels, e.g. 1.1.1.1.
   * Any versions not supplied will be zeroed.
   *
   * @param  string $version
   * @param  string $comparer
   * @return bool
   */
  public static function version_less_than($version, $comparer) {
    $version_array = explode('.', $version);
    $comparer_array = explode('.', $comparer);

    for ($i = 0; $i <= 3; $i++) {
      // zero out unset numbers
      if (!isset($version_array[$i])) { $version_array[$i] = 0; }
      if (!isset($comparer_array[$i])) { $comparer_array[$i] = 0; }

      if ($version_array[$i] < $comparer_array[$i]) {
        return true;
      }

    }
    return false;
  }

  /**
   * Determines if the first argument version is less than or equal to the second
   * argument version. A version can be up four levels, e.g. 1.1.1.1.
   * Any versions not supplied will be zeroed.
   *
   * @param  string $version
   * @param  string $comparer
   * @return bool
   */
  public static function version_less_than_or_equal_to($version, $comparer) {
    $version_array = explode('.', $version);
    $comparer_array = explode('.', $comparer);

    if ($version == $comparer || self::version_less_than($version, $comparer)) {
      return true;
    }

    return false;
  }

  /**
   * Determines if the first argument version is greater than the second
   * argument version. A version can be up four levels, e.g. 1.1.1.1.
   * Any versions not supplied will be zeroed.
   *
   * @param  string $version
   * @param  string $comparer
   * @return bool
   */
  public static function version_greater_than($version, $comparer) {
    $version_array = explode('.', $version);
    $comparer_array = explode('.', $comparer);

    for ($i = 0; $i <= 3; $i++) {
      // zero out unset numbers
      if (!isset($version_array[$i])) { $version_array[$i] = 0; }
      if (!isset($comparer_array[$i])) { $comparer_array[$i] = 0; }

      if ($version_array[$i] > $comparer_array[$i]) {
        return true;
      } elseif ($version_array[$i] < $comparer_array[$i]) {
        return false;
      }

    }
    return false;
  }

  /**
   * Determines if the first argument version is greater than or equal to the second
   * argument version. A version can be up four levels, e.g. 1.1.1.1.
   * Any versions not supplied will be zeroed.
   *
   * @param  string $version
   * @param  string $comparer
   * @return bool
   */
  public static function version_greater_than_or_equal_to($version, $comparer) {
    $version_array = explode('.', $version);
    $comparer_array = explode('.', $comparer);

    if ($version == $comparer || self::version_greater_than($version, $comparer)) {
      return true;
    }

    return false;
  }

  /**
   * This is the function that will perform the update.
   */
  public static function perform_update() {
    if (self::get_version() && intval(self::get_version()) <= 6) {
      // an update so big, it gets it's own class!
      ShareaholicSixToSeven::update();
    }
    if (self::get_option('metakey_6to7_upgraded') != 'true') {
      global $wpdb;
      $results = $wpdb->query( "UPDATE `wp_postmeta` SET `meta_key` = 'shareaholic_disable_open_graph_tags' WHERE `meta_key` = 'Hide OgTags'" );
      $results = $wpdb->query( "UPDATE `wp_postmeta` SET `meta_key` = 'shareaholic_disable_share_buttons' WHERE `meta_key` = 'Hide SexyBookmarks'" );
      self::update_options(array('disable_tracking' => 'off'));
      self::update_options(array('disable_og_tags' => 'off'));
      self::update_options(array('metakey_6to7_upgraded' => 'true'));
    }
    // any other things that need to be updated
  }

  /**
   * Return the type of page we're on as a string
   * to use for the location in the JS
   *
   * @return string
   */
  public static function page_type() {
    if (is_front_page()) {
      return 'index';
    } elseif (is_page()) {
      return 'page';
    } elseif (is_single()) {
      return 'post';
    } elseif (is_category()) {
      return 'category';
    }
  }

  /**
   * Returns the appropriate asset path for something from our
   * rails app.
   *
   * @param string $asset
   * @return string
   */
  public static function asset_url($asset) {
    if (preg_match('/spreadaholic/', Shareaholic::URL)) {
      return "http://spreadaholic.com:8080/assets-development/" . $asset;
    } elseif (preg_match('/staging\.shareaholic/', Shareaholic::URL)) {
      return '//dtym7iokkjlif.cloudfront.net/assets-staging/' . $asset;
    } else {
      return '//dtym7iokkjlif.cloudfront.net/assets/' . $asset;
    }
  }

  /**
   * Checks whether the api key has been verified
   * using the rails endpoint. Once the key has
   * been verified, we store that away so that we
   * don't have to check again.
   *
   * @return bool
   */
  public static function api_key_verified() {
    $settings = self::get_settings();
    if (isset($settings['api_key_verified']) && $settings['api_key_verified']) {
      return true;
    }

    $api_key = $settings['api_key'];
    if (!$api_key) {
      return false;
    }

    $response = ShareaholicCurl::get(Shareaholic::URL . '/publisher_tools/' . $api_key . '/verified');
    $result = $response['body'];

    if ($result == 'true') {
      ShareaholicUtilities::update_options(array(
        'api_key_verified' => true
      ));
    }
  }

  /**
   * A wrapper function to specificaly update the location name ids
   * because this is such a common function
   *
   * @todo Determine whether needed anymore
   *
   * @param array $array an array of location names to location ids
   * @return bool
   */
  public static function update_location_name_ids($array) {
    $settings = self::get_settings();
    $location_name_ids = (isset($settings['location_name_ids']) ? $settings['location_name_ids'] : array());
    $merge = array_merge($location_name_ids, $array);
    $settings['location_name_ids'] = $merge;

    update_option('shareaholic_settings', $settings);
  }

  /**
   *
   * Loads the locations names and their respective ids for an api key
   * and sets them in the shareaholic settings.'
   *
   * @param string $api_key
   */
  public static function get_new_location_name_ids($api_key) {
    $response = ShareaholicCurl::get(Shareaholic::URL . "/publisher_tools/{$api_key}.json");
    $publisher_configuration = $response['body'];
    $result = array();

    if ($publisher_configuration && is_array($publisher_configuration)) {
      foreach (array('share_buttons', 'recommendations') as $app) {
        foreach ($publisher_configuration['apps'][$app]['locations'] as $id => $location) {
          $result[$app][$location['name']] = $id;
        }
      }

      self::update_location_name_ids($result);
    } else {
      ShareaholicUtilities::load_template('failed_to_create_api_key_modal');
    }
  }

  /**
   * A general function to underscore a CamelCased string.
   *
   * @param string $string
   * @return string
   */
  public static function underscore($string) {
    return strtolower(preg_replace('/([a-z])([A-Z])', '$1_$2', $string));
  }

  /**
   * Passed an array of location names mapped to ids per app.
   *
   * @param array $array
   */
  public static function turn_on_locations($array) {
    foreach($array as $app => $ids) {
      foreach($ids as $name => $id) {
        self::update_options(array(
          $app => array($name => 'on')
        ));
      }
    }
  }

  /**
   * Returns the api key or creates a new one.
   */
  public static function get_or_create_api_key() {
    $settings = self::get_settings();
    if (isset($settings['api_key']) && !empty($settings['api_key'])) {
      return $settings['api_key'];
    }
    delete_option('shareaholic_settings');

    $verification_key = md5(mt_rand());
    $response = ShareaholicCurl::post(Shareaholic::URL . '/publisher_tools/anonymous', array(
      'configuration_publisher' => array(
        'verification_key' => $verification_key,
        'site_name' => get_bloginfo('name'),
        'domain' => self::site_url(),
        'platform' => 'wordpress',
        'shortener' => 'shrlc',
        'recommendations_attributes' => array(
          'locations_attributes' => array(
            array('name' => 'post_below_content'),
            array('name' => 'page_below_content'),
            array('name' => 'index_below_content'),
            array('name' => 'category_below_content')
          )
        ),
        'share_buttons_attributes' => array(
          'locations_attributes' => array(
            array('name' => 'post_below_content', 'counter' => 'badge-counter'),
            array('name' => 'page_below_content', 'counter' => 'badge-counter'),
            array('name' => 'index_below_content', 'counter' => 'badge-counter'),
            array('name' => 'category_below_content', 'counter' => 'badge-counter')
          )
        )
      )
    ));

    if ($response && preg_match('/20*/', $response['response']['code'])) {
      self::update_options(array(
        'api_key' => $response['body']['api_key'],
        'verification_key' => $verification_key,
        'location_name_ids' => $response['body']['location_name_ids']
      ));

      ShareaholicUtilities::turn_on_locations($response['body']['location_name_ids']);
    } else {
      add_action('admin_notices', array('ShareaholicAdmin', 'failed_to_create_api_key'));
    }
  }

  /**
   * Returns the site's url stripped of protocol.
   *
   * @return string
   */
  public static function site_url() {
    return preg_replace('/https?:\/\//', '', site_url());
  }

  /**
   * Shockingly the built in PHP array_merge_recursive function is stupid.
   * this is stolen from the PHP docs and will overwrite existing keys instead
   * of appending the values.
   *
   * http://www.php.net/manual/en/function.array-merge-recursive.php#92195
   *
   * @param  array $array1
   * @param  array $array2
   * @return array
   */
  public static function array_merge_recursive_distinct ( array &$array1, array &$array2 )
  {
    $merged = $array1;

    foreach ( $array2 as $key => &$value )
    {
      if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) )
      {
        if (empty($value)) {
          $merged[$key] = array();
        } else {
          $merged [$key] = self::array_merge_recursive_distinct ( $merged [$key], $value );
        }
      }
      else
      {
        $merged [$key] = $value;
      }
    }

    return $merged;
  }

  /**
   * Array casting an object is not recursive, this makes it recursive
   *
   * @param object $d
   *
   * http://www.if-not-true-then-false.com/2009/php-tip-convert-stdclass-object-to-multidimensional-array-and-convert-multidimensional-array-to-stdclass-object/
   */
  public static function object_to_array($d) {
    if (is_object($d)) {
      // Gets the properties of the given object
      // with get_object_vars function
      $d = get_object_vars($d);
    }

    if (is_array($d)) {
      /*
      * Return array converted to object
      */
      return array_map(array('self', 'object_to_array'), $d);
    }
    else {
      // Return array
     return $d;
    }
  }

  /**
   * This is a wrapper for the Event API
   *
   * @param string $event_name    the name of the event
   * @param array  $extra_params  any extra data points to be included
   */
   public static function log_event($event_name = 'Default', $extra_params = false) {

     global $wpdb;

     $event_metadata = array(
  		'plugin_version' => Shareaholic::VERSION,
  		'api_key' => self::get_option('api_key'),
  		'domain' => get_bloginfo('url'),
  		'language' => get_bloginfo('language'),
  		'stats' => array (
  		  'posts_total' => $wpdb->get_var( "SELECT count(ID) FROM $wpdb->posts where post_type = 'post' AND post_status = 'publish'" ),
  		  'pages_total' => $wpdb->get_var( "SELECT count(ID) FROM $wpdb->posts where post_type = 'page' AND post_status = 'publish'" ),
  		  'comments_total' => wp_count_comments()->approved,
  		  'users_total' => $wpdb->get_var("SELECT count(ID) FROM $wpdb->users"),
	      ),
  		'diagnostics' => array (
  		  'wp_version' => get_bloginfo('version'),
  		  'theme' => get_option('template'),
  		  'active_plugins' => get_option('active_plugins', array()),
  		  'multisite' => is_multisite(),
  		  ),
  		 'features' => array (
  		    'share_buttons' => self::get_option('share_buttons'),
  		    'recommendations' => self::get_option('recommendations'),
  		  )
  	  );

  	 if ($extra_params) {
  	   $event_metadata = array_merge($event_metadata, $extra_params);
  	 }

  	$event_api_url = Shareaholic::URL . '/api/events';
  	$event_params = array('name' => "WordPress:".$event_name, 'data' => json_encode($event_metadata) );

    $response = ShareaholicCurl::post($event_api_url, $event_params);
  }

  /**
   * This is a wrapper for the Recommendations Status API
   *
   */
   public static function recommendations_status_check() {
  	$recommendations_status_api_url = Shareaholic::URL . "/v2/recommendations/status?url=" . get_bloginfo('url');
    $response = ShareaholicCurl::get($recommendations_status_api_url);
    if(is_array($response) && array_key_exists('body', $response)) {
      $body = $response['body'];
      if (is_array($body) && $body['code'] == 200) {
        if ($body['data'][0]['status_code'] < 3) {
          return "processing";
        } else {
          return "ready";
        }
      } else {
        return "unknown";
      }
    }
   }

}
?>