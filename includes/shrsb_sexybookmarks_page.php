<?php

/*
*   @desc Set default options
*/

$shrsb_plugopts = shrsb_sb_set_options();

/*
 * @desc Set the settings either from database or default
 */
function shrsb_sb_set_options($action = NULL){
   
    /*
    *   @desc Most Popular Services List
    *   @note To change the most popular list also change the "Most Popular" link click handler in shareaholic-admin.js
    */
    $shrsb_most_popular = array (
			'shr-facebook',
			'shr-twitter',
			'shr-linkedin',
			'shr-googleplus',
			'shr-googlebookmarks',
			'shr-stumbleupon',
			'shr-fastmail',
			'shr-printfriendly'
    );
    
    $defaultLikeButtonOrder = array(
			'shr-fb-like',
			'shr-fb-send',
			'shr-plus-one',
			'shr-tw-button'
    );
    			               
    $shrsb_sb_plugopts_default = array(
			'sexybookmark' => '0',
			'firstrun' => '1',  
			'position' => 'below', // below, above, or manual
			'reloption' => 'nofollow', // 'nofollow', or ''
			'targetopt' => '_blank', // 'blank' or 'self'
			'perfoption' => '1', // Third party Content
			'showShareCount' => '1', // fb/twit share count
			'likeButtonSetTop' => '0', // Include like button below the Post Title
			'fbLikeButtonTop' => '0', // Include fb like button
			'fbSendButtonTop' => '0', // Include fb like button
			'googlePlusOneButtonTop' => '0', // Include Google Plus One button
			'tweetButtonTop' => '0', // Include Tweet button
			'likeButtonSetSizeTop' => "1", // Size of like buttons
			'likeButtonSetCountTop' => "true", // Show count with +1 button
			'likeButtonOrderTop' => $defaultLikeButtonOrder,
			'likeButtonSetAlignmentTop' => '0', // Alignment 0 => left, 1 => right
			'likeButtonSetBottom' => '1', // Include like button below the Post
			'fbLikeButtonBottom' => '0', // Include fb like button
			'fbSendButtonBottom' => '0', // Include fb like button
			'googlePlusOneButtonBottom' => '0', // Include Google Plus One button
			'tweetButtonBottom' => '0', // Include Tweet button
			'likeButtonSetSizeBottom' => "1", // Size of like buttons
			'likeButtonSetCountBottom' => "true", // Show count with +1 button
			'likeButtonOrderBottom' => $defaultLikeButtonOrder,
			'likeButtonSetAlignmentBottom' => '0', // Alignment 0 => left, 1 => right
			'locale'=> '0', //Default locale set to 0
			'fbNameSpace' => '1',  // Add fb name space to the html
			'preventminify' => '1',  // prevent wp_minify from minifying the js
			'shrlink' => '0', // show promo link
			'bgimg-yes' => 'yes', // 'yes' or blank
			'mobile-hide' => '', // 'yes' or blank
			'bgimg' => 'caring', // default bg image
			'shorty' => 'google', // default shortener
			'pageorpost' => 'postpageindexcategory',
			'bookmark' => $shrsb_most_popular ,//array_keys($shrsb_bookmarks_data),
			'feed' => '0', // 1 or 0
			'expand' => '1',
			'autocenter' => '1',
			'tweetconfig' => urlencode('${title} - ${short_link} via @Shareaholic'), // Custom configuration of tweet
		  'warn-choice' => '',
			'doNotIncludeJQuery' => '',
			'custom-mods' => '',
			'scriptInFooter' => '',
      'shareaholic-javascript' => '1',
      'shrbase' => 'http://www.shareaholic.com',
      'apikey' => '8afa39428933be41f8afdb8ea21a495c',
      'service' => '',
      'designer_toolTips' => '1',
      'tip_bg_color' => '#000000',  // tooltip background color
      'tip_text_color' => '#ffffff', // tooltip text color
      'spritegen_path' => SHRSB_UPLOADDIR_DEFAULT,
      'ogtags' => '1',  //Open Graph Tags
      'promo' => '1'
		);
    
        //Return default settings 
        if($action == "reset"){
            delete_option("SexyBookmarks");
            add_option("SexyBookmarks",$shrsb_sb_plugopts_default);
            return $shrsb_sb_plugopts_default;
        }

        //Get the settings from the database
        $database_Settings =  get_option('SexyBookmarks');
        
        
        if($database_Settings){//got the settings in the database
            
            // Check only when upgrading
            if(SHRSB_UPGRADING) {
                $need_to_update = false;
        
                if(!isset($database_Settings['sexybookmark']) ){
                    $database_Settings['sexybookmark'] = '1';
                    $database_Settings['firstrun'] = '0';
                    $need_to_update = true;
                }
                //For first time activation
                update_option("SHR_activate",1);

                //Check whether all the settings are present or not
                foreach($shrsb_sb_plugopts_default as $k => $v){
                    if( !array_key_exists( $k, $database_Settings)) {
                        $database_Settings[$k] = $v;
                        $need_to_update = true;
                    }
                }
                //Check for the tweetbutton in likebutton set
                if(!in_array("shr-tw-button", $database_Settings["likeButtonOrderTop"]) ) array_push($database_Settings["likeButtonOrderTop"],"shr-tw-button");
                if(!in_array("shr-tw-button", $database_Settings["likeButtonOrderBottom"]) ) array_push($database_Settings["likeButtonOrderBottom"],"shr-tw-button");

                if($need_to_update) update_option("SexyBookmarks",$database_Settings);

            }
            
            return $database_Settings;
            
        }else{
            //Add the settings
            add_option('SexyBookmarks',$shrsb_sb_plugopts_default);
            
            // Forcing the value for sexybookmark to be 1 for the first run
            $shrsb_sb_plugopts_default['firstrun'] = '1';
            return $shrsb_sb_plugopts_default;
        }
}


add_option('SHRSB_apikey', $shrsb_plugopts['apikey']);
add_option('SHRSB_CustomSprite', '');
add_option('SHRSB_DefaultSprite',true);

// If plugin is upgrading
if(SHRSB_UPGRADING == TRUE) {
    

    //Remove the Disabled Services
    if(isset ($shrsb_plugopts) && isset($shrsb_plugopts['service'])){
       $services = explode(',', $shrsb_plugopts['service']);

       if(!empty($services)){
           // Removing blocked services from sb services list
           $disable_services = array( '4', '12', '68', '77', '159', '185', '186', '195', '207', '237', '257' );
           $services = array_diff($services, $disable_services);
           $shrsb_plugopts['service'] = implode(',', $services );
       }
    }    
    if(isset ($shrsb_plugopts) && isset($shrsb_plugopts['reloption']) && $shrsb_plugopts['reloption'] === "" ){
        $shrsb_plugopts['reloption'] = '1';
    }
}

/*
*   @note Make sure spritegen_path is defined
*/

//Check for POST
if(isset($_POST['save_changes_sb']) ){
    //Define the default path for Spritegen Directory
    if(isset($_POST['spritegen_path']) && $_POST['spritegen_path'] !=  SHRSB_UPLOADDIR_DEFAULT){
        //Create the Directory
        $p = shrb_addTrailingChar(stripslashes($_POST['spritegen_path']),"/");

        define('SHRSB_UPLOADDIR', $p);
        define('SHRSB_UPLOADPATH', shr_dir_to_path($p));
    }else{
        define('SHRSB_UPLOADDIR', SHRSB_UPLOADDIR_DEFAULT);
        define('SHRSB_UPLOADPATH', SHRSB_UPLOADPATH_DEFAULT);
    }
}else{
    if( isset($_POST['reset_all_options_sb'])|| (isset($shrsb_plugopts['spritegen_path']) && $shrsb_plugopts['spritegen_path'] == SHRSB_UPLOADDIR_DEFAULT) ){
        // For Reseting the data Or First Time Install
        define('SHRSB_UPLOADDIR', SHRSB_UPLOADDIR_DEFAULT);
        define('SHRSB_UPLOADPATH', SHRSB_UPLOADPATH_DEFAULT);
    }else{
        $p = shrb_addTrailingChar(stripslashes($shrsb_plugopts['spritegen_path']),"/");
        define('SHRSB_UPLOADDIR', $p);
        define('SHRSB_UPLOADPATH', shr_dir_to_path($p));
    }
}

$shrsb_plugopts['apikey'] = get_option('SHRSB_apikey');
$shrsb_custom_sprite = get_option('SHRSB_CustomSprite');

// Some databases got corrupted. This will set things in place.
if($shrsb_plugopts['shrbase'] != 'http://www.shareaholic.com'){
    $shrsb_plugopts['shrbase'] = 'http://www.shareaholic.com';
}

?>