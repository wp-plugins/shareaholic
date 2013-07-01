<?php
/**
 * @package shareaholic
 */

/**
 * An interface to the publisher notification API
 */
class ShareaholicNotifier {
  /**
   * The url of the publisher API
   */

  const URL = 'http://api.shareaholic.com/publisher/1.0';

  /**
   * Handles publishing or updating a post
   *
   * @param  string $post_id the post id
   * @return bool   whether the request worked
   */
  public static function post_notify($post_id) {
    $post = get_post($post_id);
    $url = get_permalink($post_id);
    $tags = wp_get_post_tags($post_id, array('fields' => 'name'));

    $categories = array_map(array(self, 'post_notify_iterator'), get_the_category($post_id));

    if (has_post_thumbnail($post_id)) {
      $featured_image = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'large');
    } else {
      $featured_image = ShareaholicPublic::post_first_image();
      if (!$featured_image) {
        $featured_image = '';
      }
    }

    $notification = array(
      'url' => $url,
      'content' => array(
        'title' => $post->post_title,
        'excerpt' => $post->post_excerpt,
        'body' => $post->post_content,
        'featured-image-url' => $featured_image
      ),
      'metadata' => array(
        'author' => $post->post_author,
        'post-type' => $post->post_type,
        'post-id' => $post_id,
        'post-tags' => $tags,
        'post-categories' => $categories,
        'updated' => $post->modified,
        'visibility' => $post->post_status
    ));

    return self::send_notification($notification);
  }

  private static function post_notify_iterator($category) {
    return $category->name;
  }

  /**
   * Actually sends the request to the notification API
   *
   * @param array $notification an associative array of data
   *                            to send to the API
   */
  private static function send_notification($notification) {
    $url = self::URL . '/notify';
    $response = ShareaholicCurl::post($url, $notification, 'json');

    if ($response['result'] == 'success') {
      return true;
    } else {
      return false;
    }
  }
}

?>
