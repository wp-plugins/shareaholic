<?php
/**
 * Holds the ShareaholicPublic class.
 *
 * @package shareaholic
 */

// Get the required libraries for the Share Counts API
require_once(SHAREAHOLIC_DIR . '/lib/social-share-counts/wordpress_http.php');
require_once(SHAREAHOLIC_DIR . '/lib/social-share-counts/seq_share_count.php');
require_once(SHAREAHOLIC_DIR . '/lib/social-share-counts/curl_multi_share_count.php');
require_once(SHAREAHOLIC_DIR . '/public_js.php');

/**
 * This class is all about drawing the stuff in publishers'
 * templates that visitors can see.
 *
 * @package shareaholic
 */
class ShareaholicPublic {

  /**
   * Loads before all else
   */
  public static function init() {
    add_filter('wp_headers', array('ShareaholicUtilities', 'add_header_xua'));
  }

  /**
   * Loads before all else
   */
  public static function after_setup_theme() {
    // Ensure thumbnail/featured image support
    if(!current_theme_supports('post-thumbnails')){
      add_theme_support('post-thumbnails');
    }
  }
	
  /**
   * The function called during the wp_head action. The
   * rest of the plugin doesn't need to know exactly what happens.
  */
  public static function wp_head() {
    // this will only run on pages that would actually call
    // the deprecated functions. For some reason I could not
    // get this function to run using a hook, though that
    // should not discourage anyone in the future. -DG
    ShareaholicDeprecation::destroy_all();
    self::script_tag();
    self::tracking_meta_tag();
    self::shareaholic_tags();
    self::draw_og_tags();
  }  

  /**
   * Inserts the script code snippet into the head of the page
   */
  public static function script_tag() {
    if (ShareaholicUtilities::has_accepted_terms_of_service() &&
        ShareaholicUtilities::get_or_create_api_key()) {
      ShareaholicUtilities::load_template('script_tag', array(
        'shareaholic_url' => Shareaholic::URL,
        'api_key' => ShareaholicUtilities::get_option('api_key'),
        'page_config' => ShareaholicPublicJS::get_page_config(),
      ));
    }
  }

  /**
   * The function that gets called for shortcodes
   *
   * @param array $attributes this is passed keys: `id`, `app`, `title`, `link`, `summary`
   * @param string $content is the enclosed content (if the shortcode is used in its enclosing form)
   */
  public static function shortcode($attributes, $content = NULL) {
    extract(shortcode_atts(array(
      "id" => NULL,
      "app" => 'share_buttons',
      "title" => NULL,
      "link" => NULL,
      "summary" => NULL
    ), $attributes, 'shareaholic'));
    
    if (isset($attributes['title'])) $title = esc_attr(trim($attributes['title']));  
    if (isset($attributes['link'])) $link = trim($attributes['link']);
    if (isset($attributes['summary'])) $summary = esc_attr(trim($attributes['summary']));  
    
    return self::canvas($attributes['id'], $attributes['app'], $title, $link, $summary);
  }

  /**
   * Draws the analytics disabling meta tag, if the user
   * has asked for analytics to be disabled.
   */
  public static function tracking_meta_tag() {
    $settings = ShareaholicUtilities::get_settings();
    if ($settings['disable_tracking'] == "on") {
      echo '<meta name="shareaholic:analytics" content="disabled" />';
    }
  }
  
  
  /**
   * Draws the shareaholic meta tags.
   */
  private static function shareaholic_tags() {
    echo "\n<!-- Shareaholic Content Tags -->\n";
    self::draw_site_name_meta_tag();
    self::draw_language_meta_tag();
    self::draw_url_meta_tag();
    self::draw_keywords_meta_tag();
    self::draw_article_meta_tag();
    self::draw_site_id_meta_tag();
    self::draw_plugin_version_meta_tag();
    self::draw_image_meta_tag();
    echo "\n<!-- Shareaholic Content Tags End -->\n";
  }

  /**
   * Draws Shareaholic keywords meta tag.
   */
  private static function draw_keywords_meta_tag() {
    if (in_array(ShareaholicUtilities::page_type(), array('page', 'post'))) {
      global $post;
      $keywords = '';
      
      if (is_attachment() && $post->post_parent){
        $id = $post->post_parent;
      } else {
        $id = $post->ID;
      }
      
      // Get post tags
      $keywords = implode(', ' , ShareaholicUtilities::permalink_keywords($id));
             
      // Get post categories
      $categories_array = get_the_category($id);
      $categories = '';
      $separator = ', ';
      $output = '';
      
      if($categories_array) {
      	foreach($categories_array as $category) {
      	  if ($category->cat_name != "Uncategorized") {
      		  $output .= $separator.$category->cat_name;
    		  }
      	}
       $categories = trim($output, $separator);
      }      
      
      // Merge post tags and categories
      if ($keywords != ''){
        $keywords .= ', '.$categories;
      } else {
        $keywords .= $categories;
      }
            
      // Encode, lowercase & trim appropriately
      $keywords = ShareaholicUtilities::normalize_keywords($keywords);
      
      // Unique keywords
      $keywords_array = array();
      $keywords_array = explode(', ', $keywords);
      $keywords_array = array_unique($keywords_array);      
      $keywords_unique_list = implode(', ', $keywords_array);
      
      if ($keywords_unique_list != '' && $keywords_unique_list != "array") {
        echo "<meta name='shareaholic:keywords' content='" .  $keywords_unique_list . "' />\n";
      }
    }
  }
  
  /**
   * Draws Shareaholic article meta tags
   */
  private static function draw_article_meta_tag() {
    if (in_array(ShareaholicUtilities::page_type(), array('page', 'post'))) {
      global $post;
    
      // Article Publish and Modified Time
      $article_published_time = strtotime($post->post_date_gmt);
      $article_modified_time = strtotime(get_lastpostmodified('GMT'));
    
      if (!empty($article_published_time)) {
        echo "<meta name='shareaholic:article_published_time' content='" . date('c', $article_published_time) . "' />\n";
      }
      if (!empty($article_modified_time)) {
        echo "<meta name='shareaholic:article_modified_time' content='" . date('c', $article_modified_time) . "' />\n";
      }
      
      // Article Visibility
      $article_visibility = $post->post_status;
      $article_password = $post->post_password;

      if ($article_visibility == 'draft' || $article_visibility == 'auto-draft' || $article_visibility == 'future' || $article_visibility == 'pending'){
        echo "<meta name='shareaholic:shareable_page' content='false' />\n";
        $article_visibility = 'draft';
      } else if ($article_visibility == 'private' || $post->post_password != '' || is_attachment()) {
        echo "<meta name='shareaholic:shareable_page' content='true' />\n";
        $article_visibility = 'private';
      } else {
        echo "<meta name='shareaholic:shareable_page' content='true' />\n";
        $article_visibility = NULL;
      }

      // Lookup Metabox value
      if (get_post_meta($post->ID, 'shareaholic_exclude_recommendations', true)) {
        $article_visibility = 'private';
      }

      if (!empty($article_visibility)) {
        echo "<meta name='shareaholic:article_visibility' content='" . $article_visibility . "' />\n";
      }
            
      // Article Author Name      
      if ($post->post_author) {
        $article_author_data = get_userdata($post->post_author);
        $article_author_name = $article_author_data->display_name;
      }
      if (!empty($article_author_name)) {
        echo "<meta name='shareaholic:article_author_name' content='" . $article_author_name . "' />\n";
      }
    }
  }
  
  /**
   * Draws Shareaholic language meta tag.
   */
  private static function draw_language_meta_tag() {
    $blog_language = get_bloginfo('language');
    if (!empty($blog_language)) {
      echo "<meta name='shareaholic:language' content='" . $blog_language . "' />\n";
    }
  }

  /**
   * Draws Shareaholic url meta tag.
   */
  private static function draw_url_meta_tag() {
    if (in_array(ShareaholicUtilities::page_type(), array('page', 'post'))) {
      $url_link = get_permalink();
      echo "<meta name='shareaholic:url' content='" . $url_link . "' />\n";
    }
  }
    
  /**
   * Draws Shareaholic version meta tag.
   */
  private static function draw_plugin_version_meta_tag() {
      echo "<meta name='shareaholic:wp_version' content='" . ShareaholicUtilities::get_version() . "' />\n";
  }  
  
  /**
   * Draws Shareaholic site name meta tag.
   */
  private static function draw_site_name_meta_tag() {
    $blog_name = get_bloginfo();
    if (!empty($blog_name)) {
      echo "<meta name='shareaholic:site_name' content='" . $blog_name . "' />\n";
    }
  }

  /**
   * Draws Shareaholic site_id meta tag.
   */
  private static function draw_site_id_meta_tag() {
    $site_id = ShareaholicUtilities::get_option('api_key');
    if (!empty($site_id)) {
      echo "<meta name='shareaholic:site_id' content='" . $site_id . "' />\n";
    }
  }
  
  /**
   * Draws Shareaholic image meta tag. Will only run on pages or posts.
   */
  private static function draw_image_meta_tag() {
    if (in_array(ShareaholicUtilities::page_type(), array('page', 'post'))) {
      global $post;
      $thumbnail_src = '';
      
      if (function_exists('has_post_thumbnail') && has_post_thumbnail($post->ID)) {
        $thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'large');
        $thumbnail_src = esc_attr($thumbnail[0]);
      } 
      if ($thumbnail_src == NULL) {
        $thumbnail_src = self::post_first_image();
      }
      if ($thumbnail_src != NULL) {
        echo "<meta name='shareaholic:image' content='" . $thumbnail_src . "' />";
      }
    }
  }

  /**
   * Draws an open graph image meta tag if they are enabled and exist. Will only run on pages or posts.
   */
  private static function draw_og_tags() {
    if (in_array(ShareaholicUtilities::page_type(), array('page', 'post'))) {
      global $post;
      $thumbnail_src = '';
      $settings = ShareaholicUtilities::get_settings();
      if (!get_post_meta($post->ID, 'shareaholic_disable_open_graph_tags', true) && (isset($settings['disable_og_tags']) && $settings['disable_og_tags'] == "off")) {        
        if (function_exists('has_post_thumbnail') && has_post_thumbnail($post->ID)) {
          $thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'large');
          $thumbnail_src = esc_attr($thumbnail[0]);
        } 
        if ($thumbnail_src == NULL) {
          $thumbnail_src = self::post_first_image();
        }
        if ($thumbnail_src != NULL) {
          echo "\n<!-- Shareaholic Open Graph Tags -->\n";
          echo "<meta property='og:image' content='" . $thumbnail_src . "' />";
          echo "\n<!-- Shareaholic Open Graph Tags End -->\n";
        }
      }
    }
  }

  /**
   * This will grab the URL of the first image in a given post
   *
   * @return returns `false` or a string of the image src
   */
  public static function post_first_image() {
    global $post;
    $first_img = '';
    if ($post == NULL)
      return false;
    else {      
      $output = preg_match_all('/<img[^>]+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
      if(isset($matches[1][0]) ){
          $first_img = $matches[1][0];
      } else {
        return false;
      }
      return $first_img;
    }
  }
	
  /**
   * This static function inserts the shareaholic canvas at the end of the post
   *
   * @param  string $content the wordpress content
   * @return string          the content
   */
  public static function draw_canvases($content) {
    global $post;
    $settings = ShareaholicUtilities::get_settings();
    $page_type = ShareaholicUtilities::page_type();
    foreach (array('share_buttons', 'recommendations') as $app) {
      if (!get_post_meta($post->ID, "shareaholic_disable_{$app}", true)) {
        if (isset($settings[$app]["{$page_type}_above_content"]) &&
            $settings[$app]["{$page_type}_above_content"] == 'on') {
          // share_buttons_post_above_content
          $id = $settings['location_name_ids'][$app]["{$page_type}_above_content"];
          $content = self::canvas($id, $app) . $content;
        }

        if (isset($settings[$app]["{$page_type}_below_content"]) &&
            $settings[$app]["{$page_type}_below_content"] == 'on') {
          // share_buttons_post_below_content
          $id = $settings['location_name_ids'][$app]["{$page_type}_below_content"];
          $content .= self::canvas($id, $app);
        }
      }
    }

    // something that uses the_content hook must return the $content
    return $content;
  }

  /**
   * Draws an individual canvas given a specific location
   * id and app. The app isn't strictly necessary, but is
   * being kept for now for backwards compatability.
   * This method was private, but was made public to be accessed
   * by the shortcode static function in global_functions.php.
   *
   * @param string $id  the location id for configuration
   * @param string $app the type of app
   * @param string $title the title of URL
   * @param string $link url
   * @param string $summary summary text for URL
   */
  public static function canvas($id, $app, $title = NULL, $link = NULL, $summary = NULL) {
    global $post, $wp_query;
    
    $data_title = ((trim($title) != NULL) ? $title : htmlspecialchars($post->post_title, ENT_QUOTES));
    $data_link = ((trim($link) != NULL) ? trim($link) : get_permalink($post->ID));
    $data_summary = ((trim($summary) != NULL) ? $summary : htmlspecialchars(strip_tags(strip_shortcodes($post->post_excerpt)), ENT_QUOTES));
    
    $canvas = "<div class='shareaholic-canvas'
      data-app-id='$id'
      data-app='$app'
      data-title='$data_title'
      data-link='$data_link'
      data-summary='$data_summary'></div>";

    return trim(preg_replace('/\s+/', ' ', $canvas));
  }


  /**
   * Function to handle the share count API requests
   *
   */
  public static function share_counts_api() {
    $cache_key = 'shr_api_res-' . md5( $_SERVER['QUERY_STRING'] );
    $result = get_transient($cache_key);

    if (!$result) {
      $url = isset($_GET['url']) ? $_GET['url'] : NULL;
      $services = isset($_GET['services']) ? $_GET['services'] : NULL;
      $result = array();

      if(is_array($services) && count($services) > 0 && !empty($url)) {
        if(self::has_curl()) {
          $shares = new ShareaholicCurlMultiShareCount($url, $services);
        } else {
          $shares = new ShareaholicSeqShareCount($url, $services);
        }
        $result = $shares->get_counts();

        if (isset($result['data'])) {
          set_transient( $cache_key, $result, SHARE_COUNTS_CHECK_CACHE_LENGTH );
        }
      }
    }

    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
  }

  /**
   * Function to return relevant plugin debug info
   *
   * @return debug info in JSON
   */
  public static function debug_info() {
    global $wpdb;
        
    if (ShareaholicUtilities::get_option('disable_tracking') == NULL || ShareaholicUtilities::get_option('disable_tracking') == "off"){
      $analytics_status =  "on";
    } else {
      $analytics_status =  "off";
    }
    
    if (ShareaholicUtilities::get_option('disable_internal_share_counts_api') == NULL || ShareaholicUtilities::get_option('disable_internal_share_counts_api') == "off"){
      $server_side_share_count_status =  "on";
    } else {
      $server_side_share_count_status =  "off";
    }
    
    if (ShareaholicUtilities::has_accepted_terms_of_service() == 1){
      $tos_status = "accepted";
    } else {
      $tos_status = "pending";
    }
    
    if (function_exists('curl_version')){
      $curl_version = curl_version();
    }
    
    $info = array(
  	'plugin_version' => Shareaholic::VERSION,
  	'site_id' => ShareaholicUtilities::get_option('api_key'),
  	'domain' => get_bloginfo('url'),
  	'language' => get_bloginfo('language'),
  	'tos_status' => $tos_status,
  	'stats' => array (
  	  'posts_total' => $wpdb->get_var( "SELECT count(ID) FROM $wpdb->posts where post_type = 'post' AND post_status = 'publish'" ),
  	  'pages_total' => $wpdb->get_var( "SELECT count(ID) FROM $wpdb->posts where post_type = 'page' AND post_status = 'publish'" ),
  	  'comments_total' => wp_count_comments()->approved,
  	  'users_total' => $wpdb->get_var("SELECT count(ID) FROM $wpdb->users"),
      ),
  	'diagnostics' => array (
  	  'theme' => get_option('template'),
    	'multisite' => is_multisite(),
    	'shareaholic_server_reachable' => ShareaholicUtilities::connectivity_check(),
    	'server_side_share_count_api_reachable' => ShareaholicUtilities::share_counts_api_connectivity_check(),
  	  'php_version' => phpversion(),
  	  'wp_version' => get_bloginfo('version'),
  	  'curl' => array (
  	    'status' => ShareaholicPublic::has_curl(),
  	    'version' => $curl_version,
  	  ),
  	'plugins' => array (
  	    'active' => get_option('active_plugins', array()),
  	    'sitewide'  => get_site_option('active_sitewide_plugins', array()),
	      ),
  	  ),
  	'app_locations' => array (
  	  'share_buttons' => ShareaholicUtilities::get_option('share_buttons'),
  	  'recommendations' => ShareaholicUtilities::get_option('recommendations'),
	    ),
  	'advanced_settings' => array (
  	  'analytics' => $analytics_status,
  	  'server_side_share_count_api' => $server_side_share_count_status,
  	  )
    );
    
    header('Content-Type: application/json');
    echo json_encode($info);
    exit;
  }
  
  
  /**
   * Function to return list of permalinks
   *
   * @return list of permalinks in JSON or plain text
   */
  public static function permalink_list(){
    
    // Input Params
    $post_type = isset($_GET['post_type']) ? $_GET['post_type'] : "any";
    $n = isset($_GET['n']) ? $_GET['n'] : -1;
    $format = isset($_GET['format']) ? $_GET['format'] : "json";
    
    $permalink_list = array();
    $permalink_query = "post_type=$post_type&post_status=publish&posts_per_page=$n";
    $posts = new WP_Query ($permalink_query);
    $posts = $posts->posts;
    foreach($posts as $post){
      switch ($post->post_type){
        case 'revision':
        case 'nav_menu_item':
          break;
        case 'page':
          $permalink = get_page_link($post->ID);
          array_push($permalink_list, $permalink);
          break;
        case 'post':
          $permalink = get_permalink($post->ID);
          array_push($permalink_list, $permalink);
          break;
        case 'attachment':
          break;
        default:
          $permalink = get_post_permalink($post->ID);
          array_push($permalink_list, $permalink);
          break;
        }
      }
      
      if ($format == "text"){
        header('Content-Type: text/plain; charset=utf-8');
        foreach($permalink_list as $link) {
          echo $link. "\r\n";
        }
      } elseif ($format == "json"){
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($permalink_list);
      }
      exit;
  }
  
  /**
   * Function to return relevant info for a given permalink for the Related Content index
   *
   * @return page info in JSON
   */
  public static function permalink_info() {   
    global $wpdb, $post;
    
    // Input Params
    $permalink = isset($_GET['permalink']) ? $_GET['permalink'] : NULL;
    $body_text = isset($_GET['body_text']) ? $_GET['body_text'] : "raw";
    
    if ($permalink == NULL){
      return;
    }
    
    // Get post ID
    $post_id = url_to_postid($permalink);
    
    // for non-default paths - handle both https and http versions of the permalink
    if ($post_id == 0){
      $parse = parse_url($permalink);
      if ($parse['scheme'] == "https"){
        $permalink = str_replace("https", "http", $permalink);
        $post_id = url_to_postid($permalink);
      } else if ($parse['scheme'] == "http"){
        $permalink = str_replace("http", "https", $permalink);
        $post_id = url_to_postid($permalink);
      }
    }
    
    if ($post_id == 0){
      return;
    }
    
    // Get post for given ID
    $post = get_post($post_id);
    
    if ($post->post_status != 'publish' || $post->post_password != ''){
      return;
    }
    
    // Post tags
    $tags = ShareaholicUtilities::permalink_keywords($post_id);

    // Post categories
    $categories = array();
    $categories_array = get_the_category($post_id);
    
    if($categories_array) {
    	foreach($categories_array as $category) {
    	  if ($category->cat_name != "Uncategorized") {
    	    $category_name = ShareaholicUtilities::normalize_keywords($category->cat_name);
          array_push($categories, $category_name);
  		  }
    	}
    }
    
    // Post thumbnail		
    $thumbnail_src = '';
    if (function_exists('has_post_thumbnail') && has_post_thumbnail($post_id)) {
      $thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'large');
      $thumbnail_src = esc_attr($thumbnail[0]);
    }
    
    if ($thumbnail_src == NULL) {
      $thumbnail_src = ShareaholicPublic::post_first_image();
    }
    
    // Post body
    $order   = array("&nbsp;", "\r\n", "\n", "\r", "  ");
    $post_body = str_replace($order, ' ', $post->post_content);
    
    if ($body_text == "clean"){
      $post_body = strip_tags($post_body);
    } elseif ($body_text == "raw" || $body_text == NULL) {
      $post_body = $post_body;
    }

    // Get post author name
    if ($post->post_author) {
      $author_data = get_userdata($post->post_author);
      $author_name = $author_data->display_name;
    }
    
    // Term frequencies
    // $term_frequency_title = array_count_values(str_word_count(strtolower(strip_tags($post->post_title)), 1));
    $term_frequency_body = array_count_values(str_word_count(strtolower(strip_tags($post_body)), 1));
    
    $term_frequency = $term_frequency_body;
    arsort($term_frequency);
    
    // Construct array
    $info = array(
      'permalink' => $permalink,
      'domain' => get_bloginfo('url'),
      'site_id' => ShareaholicUtilities::get_option('api_key'),
      'content' => array(
        'title' => $post->post_title,
        'excerpt' => $post->post_excerpt,
        'body' => $post_body,
        'thumbnail' => $thumbnail_src,
      ),
      'post_metadata' => array(
        'author_id' => $post->post_author,
        'author_name' => $author_name,
        'post_type' => $post->post_type,
        'post_id' => $post_id,
        'post_tags' => $tags,
        'post_categories' => $categories,
        'post_language' => get_bloginfo('language'),
        'post_published' => date('c', strtotime($post->post_date_gmt)),
        'post_updated' => date('c', strtotime(get_lastpostmodified('GMT'))),
        'post_visibility' => $post->post_status,
      ),
      'post_stats' => array(
        'post_comments_count' => get_comments_number($post_id),
        'post_content_title_character_count' => strlen(trim(html_entity_decode($post->post_title))),
        'post_content_title_word_count' => str_word_count(strip_tags($post->post_title)),
        'post_content_body_character_count' => strlen(trim(html_entity_decode($post_body))),
        'post_content_body_word_count' => str_word_count(strip_tags($post_body)),
        'term_frequency' => $term_frequency,
      ),
      'diagnostics' => array(
        'platform' => 'wp',
        'platform_version' => get_bloginfo('version'),
        'plugin_version' => Shareaholic::VERSION,
      ),
    );
    
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($info);
    exit;
  }
  
  /**
   * Checks to see if curl is installed
   *
   * @return bool true or false that curl is installed
   */
  public static function has_curl(){
    return function_exists('curl_version') && function_exists('curl_multi_init') && function_exists('curl_multi_add_handle') && function_exists('curl_multi_exec');
  }
}

?>