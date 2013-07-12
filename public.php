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
   * The function that gets called for shortcoding.
   *
   * @param array $attributes this is passed two keys: `app` and `name`
   */
  public static function shortcode($attributes) {
    return self::canvas($attributes['id'], $attributes['app']);
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
    echo "\n<!-- Shareaholic Content Tags (v" . ShareaholicUtilities::get_version() . ") -->\n";
    self::draw_site_name_meta_tag();
    self::draw_language_meta_tag();
    self::draw_image_meta_tag();
    echo "\n<!-- Shareaholic Content Tags End -->\n";
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
   * Draws Shareaholic site name meta tag.
   */
  private static function draw_site_name_meta_tag() {
    $blog_name = get_bloginfo();
    if (!empty($blog_name)) {
      echo "<meta name='shareaholic:site_name' content='" . $blog_name . "' />\n";
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

      if (!get_post_meta($post->ID, 'shareaholic_disable_open_graph_tags', true) && $settings['disable_og_tags'] == "off") {
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
   */
  public static function canvas($id, $app) {
    global $post, $wp_query;
    $page_type = ShareaholicUtilities::page_type();
    $canvas = "<div class='shareaholic-canvas'
      data-app-id='$id'
      data-app='$app'
      data-title='" . htmlspecialchars($post->post_title, ENT_QUOTES) . "'
      data-link='" . get_permalink($post->ID) . "'
      data-summary='" . urlencode(strip_tags(strip_shortcodes($post->post_excerpt))) . "'></div>";

    return trim(preg_replace('/\s+/', ' ', $canvas));
  }
}

?>