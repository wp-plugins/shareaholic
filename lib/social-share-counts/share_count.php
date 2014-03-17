<?php
/**
 * Shareaholic's Social Share Counts Library
 *
 * https://github.com/shareaholic/social-share-counts
 *
 * @package shareaholic
 * @version 1.0.0.0
 */

/**
 * An abstract class Share Counts to be extended
 *
 * @package shareaholic
 */
abstract class ShareaholicShareCount {

  protected $url;
  protected $services;

  public function __construct($url, $services) {
    $this->url = $url;
    $this->services = $services;
  }

  public function get_services_config() {
    return array(
      'facebook' => array(
        'url' => 'https://api.facebook.com/method/links.getStats?format=json&urls=%s',
        'method' => 'GET',
        'callback' => 'facebook_count_callback',
      ),
      'twitter' => array(
        'url' => 'http://cdn.api.twitter.com/1/urls/count.json?url=%s',
        'method' => 'GET',
        'callback' => 'twitter_count_callback',
      ),
      'linkedin' => array(
        'url' => 'http://www.linkedin.com/countserv/count/share?format=json&url=%s',
        'method' => 'GET',
        'callback' => 'linkedin_count_callback',
      ),
      'google_plus' => array(
        'url' => 'https://clients6.google.com/rpc',
        'method' => 'POST',
        'headers' => array('Content-Type' => 'application/json'),
        'body' => NULL,
        'prepare' => 'google_plus_prepare_request',
        'callback' => 'google_plus_count_callback',
      ),
      'delicious' => array(
        'url' => 'http://feeds.delicious.com/v2/json/urlinfo/data?url=%s',
        'method' => 'GET',
        'callback' => 'delicious_count_callback',
      ),
      'pinterest' => array(
        'url' => 'http://api.pinterest.com/v1/urls/count.json?url=%s&callback=f',
        'method' => 'GET',
        'callback' => 'pinterest_count_callback',
      ),
      'buffer' => array(
        'url' => 'https://api.bufferapp.com/1/links/shares.json?url=%s',
        'method' => 'GET',
        'callback' => 'buffer_count_callback',
      ),
      'stumbleupon' => array(
        'url' => 'http://www.stumbleupon.com/services/1.01/badge.getinfo?url=%s',
        'method' => 'GET',
        'callback' => 'stumbleupon_count_callback',
      ),
      'reddit' => array(
        'url' => 'http://buttons.reddit.com/button_info.json?url=%s',
        'method' => 'GET',
        'callback' => 'reddit_count_callback',
      ),
      'vk' => array(
        'url' => 'http://vk.com/share.php?act=count&url=%s',
        'method' => 'GET',
        'callback' => 'vk_count_callback',
      ),
      'odnoklassniki' => array(
        'url' => 'http://www.odnoklassniki.ru/dk?st.cmd=extLike&uid=odklcnt0&ref=%s',
        'method' => 'GET',
        'callback' => 'odnoklassniki_count_callback',
      ),
    );
  }


  /**
   * Callback function for facebook count API
   * Gets the facebook counts from response
   *
   * @param Array $response The response from calling the API
   * @return Integer The counts from the API
   */
  public function facebook_count_callback($response) {
    if(!$response || !preg_match('/20*/', $response['response']['code'])) {
      return 0;
    }
    $body = json_decode($response['body'], true);
    return isset($body[0]['total_count']) ? $body[0]['total_count'] : 0;
  }


  /**
   * Callback function for twitter count API
   * Gets the twitter counts from response
   *
   * @param Array $response The response from calling the API
   * @return Integer The counts from the API
   */
  public function twitter_count_callback($response) {
    if(!$response || !preg_match('/20*/', $response['response']['code'])) {
      return 0;
    }
    $body = json_decode($response['body'], true);
    return isset($body['count']) ? $body['count'] : 0;
  }


  /**
   * Callback function for linkedin count API
   * Gets the linkedin counts from response
   *
   * @param Array $response The response from calling the API
   * @return Integer The counts from the API
   */
  public function linkedin_count_callback($response) {
    if(!$response || !preg_match('/20*/', $response['response']['code'])) {
      return 0;
    }
    $body = json_decode($response['body'], true);
    return isset($body['count']) ? $body['count'] : 0;
  }


  /**
   * A preprocess function to be called necessary to prepare
   * the request to the service.
   *
   * One may customize the headers or body to their liking
   * before the request is sent. The customization should
   * update the services config where it will be read by
   * the get_counts() function
   *
   * @param $url The url needed by google_plus to be passed in to the body
   * @param $config The services configuration object to be updated
   */
  public function google_plus_prepare_request($url, &$config) {
    $post_fields = array(
      array(
        'method' => 'pos.plusones.get',
        'id' => 'p',
        'params' => array(
          'nolog' => true,
          'id' => $url,
          'source' => 'widget',
          'userId' => '@viewer',
          'groupId' => '@self',
        ),
        'jsonrpc' => '2.0',
        'key' => 'p',
        'apiVersion' => 'v1',
      )
    );

    $config['google_plus']['body'] = $post_fields;
  }


  /**
   * Callback function for google plus count API
   * Gets the google plus counts from response
   *
   * @param Array $response The response from calling the API
   * @return Integer The counts from the API
   */
  public function google_plus_count_callback($response) {
    if(!$response || !preg_match('/20*/', $response['response']['code'])) {
       return 0;
    }
    $body = json_decode($response['body'], true);
    return isset($body[0]['result']['metadata']['globalCounts']['count']) ? intval($body[0]['result']['metadata']['globalCounts']['count']) : 0;
  }


  /**
   * Callback function for delicious count API
   * Gets the delicious counts from response
   *
   * @param Array $response The response from calling the API
   * @return Integer The counts from the API
   */
  public function delicious_count_callback($response) {
    if(!$response || !preg_match('/20*/', $response['response']['code'])) {
      return 0;
    }
    $body = json_decode($response['body'], true);
    return isset($body[0]['total_posts']) ? $body[0]['total_posts'] : 0;
  }


  /**
   * Callback function for pinterest count API
   * Gets the pinterest counts from response
   *
   * @param Array $response The response from calling the API
   * @return Integer The counts from the API
   */
  public function pinterest_count_callback($response) {
    if(!$response || !preg_match('/20*/', $response['response']['code'])) {
      return 0;
    }
    $response['body'] = substr($response['body'], 2, strlen($response['body']) - 3);
    $body = json_decode($response['body'], true);
    return isset($body['count']) ? $body['count'] : 0;
  }


  /**
   * Callback function for buffer count API
   * Gets the buffer share counts from response
   *
   * @param Array $response The response from calling the API
   * @return Integer The counts from the API
   */
  public function buffer_count_callback($response) {
    if(!$response || !preg_match('/20*/', $response['response']['code'])) {
      return 0;
    }
    $body = json_decode($response['body'], true);
    return isset($body['shares']) ? $body['shares'] : 0;
  }


  /**
   * Callback function for stumbleupon count API
   * Gets the stumbleupon counts from response
   *
   * @param Array $response The response from calling the API
   * @return Integer The counts from the API
   */
  public function stumbleupon_count_callback($response) {
    if(!$response || !preg_match('/20*/', $response['response']['code'])) {
      return 0;
    }
    $body = json_decode($response['body'], true);
    return isset($body['result']['views']) ? $body['result']['views'] : 0;
  }


  /**
   * Callback function for reddit count API
   * Gets the reddit counts from response
   *
   * @param Array $response The response from calling the API
   * @return Integer The counts from the API
   */
  public function reddit_count_callback($response) {
    if(!$response || !preg_match('/20*/', $response['response']['code'])) {
      return 0;
    }
    $body = json_decode($response['body'], true);
    return isset($body['data']['children'][0]['data']['ups']) ? $body['data']['children'][0]['data']['ups'] : 0;
  }


  /**
   * Callback function for vk count API
   * Gets the vk counts from response
   *
   * @param Array $response The response from calling the API
   * @return Integer The counts from the API
   */
  public function vk_count_callback($response) {
    if(!$response || !preg_match('/20*/', $response['response']['code'])) {
      return 0;
    }

    // This API does not return JSON. Just plain text JS. Example:
    // 'VK.Share.count(0, 3779);'
    // From documentation, need to just grab the 2nd param: http://vk.com/developers.php?oid=-17680044&p=Share
    $matches = array();
    preg_match('/^VK\.Share\.count\(\d, (\d+)\);$/i', $response['body'], $matches);
    return isset($matches[1]) ? intval($matches[1]) : 0;
  }


  /**
   * Callback function for odnoklassniki count API
   * Gets the odnoklassniki counts from response
   *
   * @param Array $response The response from calling the API
   * @return Integer The counts from the API
   */
  public function odnoklassniki_count_callback($response) {
    if(!$response || !preg_match('/20*/', $response['response']['code'])) {
      return 0;
    }

    // Another weird API. Similar to vk, extract the 2nd param from the response:
    // 'ODKL.updateCount('odklcnt0','14198');'
    $matches = array();
    preg_match('/^ODKL\.updateCount\(\'odklcnt0\',\'(\d+)\'\);$/i', $response['body'], $matches);
    return isset($matches[1]) ? intval($matches[1]) : 0;
  }


  /**
   * The abstract function to be implemented by its children
   * This function should get all the counts for the
   * supported services
   *
   * It should return an associative array with the services as
   * the keys and the counts as the value.
   *
   * Example:
   * array('facebook' => 12, 'google_plus' => 0, 'twitter' => 14, ...);
   *
   * @return Array an associative array of service => counts
   */
  public abstract function get_counts();

}