<?php
/**
 * This will hold all of the global namespaced functions.
 *
 * @package shareaholic
 */

function selfserv_shareaholic() {
  $trace = debug_backtrace();
  $deprecation = new ShareaholicDeprecation('selfserv_shareaholic');
  $deprecation->push($trace[0]['file'], $trace[0]['line']);
  echo ShareaholicPublic::canvas(NULL, 'share_buttons');
}

function get_shr_like_buttonset($position) {
  $trace = debug_backtrace();
  $deprecation = new ShareaholicDeprecation('get_shr_like_buttonset');
  $deprecation->push($trace[0]['file'], $trace[0]['line']);

  $settings = ShareaholicUtilities::get_settings();
  $page_type = ShareaholicUtilities::page_type();

  switch ($position) {
    case 'Top':
      $id = isset($settings['location_name_ids']["{$page_type}_above_content"])
        ? $settings['location_name_ids']["{$page_type}_above_content"] : NULL;
      break;
    case 'Bottom':
      $id = isset($settings['location_name_ids']["{$page_type}_below_content"])
        ? $settings['location_name_ids']["{$page_type}_below_content"] : NULL;
      break;
  }

  echo ShareaholicPublic::canvas($id, 'share_buttons');
}

?>
