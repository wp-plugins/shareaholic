<?php

require_once('curl_multi_share_count.php');

class ShareaholicCurlMultiShareCountsTest extends PHPUnit_Framework_TestCase
{
  public function setUp() {
    $this->url = 'https://blog.shareaholic.com';
    $counts = new ShareaholicCurlMultiShareCount($this->url, array());
    $this->services = array_keys($counts->get_services_config());

    // all callbacks take a predefined response structure
    $this->response = array(
      'response' => array(
        'code' => 200
      ),
    );
  }

  public function tearDown() {

  }


  public function testGetCount() {
    // test that this function returns the expected API response
    $share_count = new ShareaholicCurlMultiShareCount($this->url, $this->services);
    $response = $share_count->get_counts();

    $this->assertNotNull($response, 'The response array should not be null');

    foreach($this->services as $service) {
      $this->assertNotNull($response['data'][$service], 'The ' . $service . ' count should not be null');
    }
  }

}
