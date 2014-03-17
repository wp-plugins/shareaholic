<?php
/**
 * This file holds the ShareaholicHttp class.
 *
 * @package shareaholic
 */

/**
 * The purpose of this class is to provide an interface around any native
 * http function (wp_remote_get, drupal_http_request, curl) so that one
 * use this consistent API for making http request with well defined input
 * and output.
 *
 * @package shareaholic
 */
class ShareaholicHttp {

  /**
   * Performs a HTTP request with a url and array of options
   *
   *
   * The options object is an associative array that takes the following options:
   * - method: The http method for the request as a string. Defaults is 'GET'.
   *
   * - headers: The headers to send with the request as an associative array of name/value pairs. Default is empty array.
   *
   * - body: The body to send with the request as an associative array of name/value pairs. Default is NULL.
   * If the body is meant to be parsed as json, specify the content type in the headers option to be 'application/json'.
   *
   * - redirection: The number of redirects to follow for this request as an integer, Default is 5.
   *
   * - timeout: The number of seconds the request should take as an integer. Default is 15 (seconds).
   *
   * - user-agent: The useragent for the request. Default is mozilla browser useragent.
   *
   *
   * This function returns an object of the response.
   * The object is an associative array with the following keys:
   * - body: the response body as a string
   * - response: an array with the following keys:
   *    - code: the response code
   *
   *
   * @param string $url The url you are sending the request to
   * @param array $options An array of supported options to pass to the request
   *
   * @return array It returns an associative array of name value pairs
   */
  public static function send($url, $options = array()) {
    return self::send_with_curl($url, $options);
  }

  private static function send_with_curl($url, $options) {
    $curl_handle = curl_init($url);

    curl_setopt($curl_handle, CURLOPT_HEADER, 0);
    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);

    // set the timeout
    $timeout = isset($options['timeout']) ? $options['timeout'] : 15;
    curl_setopt($curl_handle, CURLOPT_TIMEOUT, $timeout);

    // set the http method: default is GET
    if($option['method'] === 'POST') {
      curl_setopt($curl_handle, CURLOPT_POST, 1);
    }

    // set the body and headers
    $headers = isset($options['headers']) ? $options['headers'] : array();
    $body = isset($options['body']) ? $options['body'] : NULL;

    if(isset($body)) {
      if(isset($headers['Content-Type']) && $headers['Content-Type'] === 'application/json') {
        $data_string = json_encode($body);

        curl_setopt($curl_handle, CURLOPT_HTTPHEADER, array(
          'Content-Type: application/json',
          'Content-Length: ' . strlen($data_string))
        );

        curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $data_string);
      }
    }

    // set the useragent
    $useragent = isset($options['user-agent']) ? $options['user-agent'] : 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:24.0) Gecko/20100101 Firefox/24.0';
    curl_setopt($curl_handle, CURLOPT_USERAGENT, $useragent);

    // set the max redirects
    if(isset($options['redirection'])) {
      curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($curl_handle, CURLOPT_MAXREDIRS, $option['redirection']);
    }

    $output = curl_exec($curl_handle);

    $result['body'] = $output;
    $result['response'] = array(
      'code' => curl_getinfo($curl_handle, CURLINFO_HTTP_CODE),
    );

    curl_close($curl_handle);
    return $result;
  }
}
