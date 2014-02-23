<?php
/**
 * Holds the ShareaholicPublic class.
 *
 * @package shareaholic
 */

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
        'api_key' => ShareaholicUtilities::get_option('api_key')
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
      $keywords = implode(', ', wp_get_post_tags( $id, array('fields' => 'names') ) );      
             
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
      
      // Support for "All in One SEO Pack" plugin keywords
      if (get_post_meta($post->ID, '_aioseop_keywords') != NULL){
        $keywords .= ', '.stripslashes(get_post_meta($post->ID, '_aioseop_keywords', true));
      }
      
      // Support for "WordPress SEO by Yoast" plugin keywords
      if (get_post_meta($post->ID, '_yoast_wpseo_focuskw') != NULL){
        $keywords .= ', '.stripslashes(get_post_meta($post->ID, '_yoast_wpseo_focuskw', true));
      }
      
      if (get_post_meta($post->ID, '_yoast_wpseo_metakeywords') != NULL){
        $keywords .= ', '.stripslashes(get_post_meta($post->ID, '_yoast_wpseo_metakeywords', true));
      }
      
      // Support for "Add Meta Tags" plugin keywords
      if (get_post_meta($post->ID, '_amt_keywords') != NULL){
        $keywords .= ', '.stripslashes(get_post_meta($post->ID, '_amt_keywords', true));
      }
 
      if (get_post_meta($post->ID, '_amt_news_keywords') != NULL){
        $keywords .= ', '.stripslashes(get_post_meta($post->ID, '_amt_news_keywords', true));
      }     
      
      // Encode, lowercase & trim appropriately
      $keywords = trim(trim(strtolower(trim(htmlspecialchars(htmlspecialchars_decode($keywords), ENT_QUOTES))), ","));

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

      if ($article_visibility == 'draft' || $article_visibility == 'auto-draft'){
        $article_visibility = 'draft';
      } else if ($article_visibility == 'private' || $post->post_password != '' || is_attachment()) {
        $article_visibility = 'private';
      } else {
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
   * Draws Shareaholic image tag. Will only run on pages or posts.
   */
  private static function draw_image_meta_tag() {
    global $post;
    if (in_array(ShareaholicUtilities::page_type(), array('page', 'post'))) {
      if (has_post_thumbnail($post->ID)) {
        $thumbnail_src = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'large');
        echo "<meta name='shareaholic:image' content='" . esc_attr($thumbnail_src[0]) . "' />";
      } else {
        if ($first_image = self::post_first_image()) {
          echo "<meta name='shareaholic:image' content='" . $first_image . "' />";
        }
      }
    }
  }

  /**
   * Draws og image tags if they are enabled and exist. Will only run on pages or posts.
   */
  private static function draw_og_tags() {
    $settings = ShareaholicUtilities::get_settings();

    if (in_array(ShareaholicUtilities::page_type(), array('page', 'post'))) {
      global $post;

      if (!get_post_meta($post->ID, 'shareaholic_disable_open_graph_tags', true) && (isset($settings['disable_og_tags']) && $settings['disable_og_tags'] == "off")) {
        if (has_post_thumbnail($post->ID)) {
          $thumbnail_src = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'large');
          echo "\n<!-- Shareaholic Open Graph Tags -->\n";
          echo "<meta property='og:image' content='" . esc_attr($thumbnail_src[0]) . "' />";
          echo "\n<!-- Shareaholic Open Graph Tags End -->\n";
        } else {
          $first_image = self::post_first_image();
          if ($first_image) {
            echo "\n<!-- Shareaholic Open Graph Tags -->\n";
            echo "<meta property='og:image' content='" . $first_image . "' />";
            echo "\n<!-- Shareaholic Open Graph Tags End -->\n";
          }
        }
      }
    }
  }

  /**
   * Copied straight out of the old wordpress version, this will grab the
   * first image in a post. Not sure why the output buffering is needed,
   * should probably be removed.
   *
   * @return mixed either returns `false` or a string of the image src
   */
  public static function post_first_image() {
    global $post, $posts;
    $og_first_img = '';
    ob_start();
    ob_end_clean();
    if ($post == null)
      return false;
    else {
      $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
      if(isset($matches) && isset($matches[1]) && isset($matches[1][0]) ){
          $og_first_img = $matches[1][0];
      }
      if(empty($og_first_img)){ // return false if nothing there, makes life easier
        return false;
      }
      return $og_first_img;
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
}

?>