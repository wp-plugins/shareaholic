<?php

/* 
 * @desc Recommendations Settings page
*/

function shrsb_recommendations_settings_page() {
	global $shrsb_recommendations;
  
    // Add all the global varaible declarations for the $shrsb_recommendations_plugopts
	echo '<div class="wrap""><div class="icon32" id="icon-options-general"><br></div><h2>'.__('Recommendations: Related Content Settings', 'shrsb').'</h2></div>';
    //Defaults - set if not present
    if (!isset($_POST['reset_all_options_recommendations'])){$_POST['reset_all_options_recommendations'] = '1';}
    if (!isset($_POST['shrsbresetallwarn-choice'])){$_POST['shrsbresetallwarn-choice'] = 'no';}
    
	if($_POST['reset_all_options_recommendations'] == '0') {
		echo '
		<div id="shrsbresetallwarn" class="dialog-box-warning" style="float:none;width:97%;margin-top:20px;">
			<div class="dialog-left fugue f-warn">
				'.__("WARNING: You are about to reset all plugin settings to their default state! Do you wish to continue?", "shrsb").'
			</div>
			<div class="dialog-right">
				<form action="" method="post" id="resetalloptionsaccept">
					<label><input name="shrsbresetallwarn-choice" id="shrsbresetallwarn-yes" type="radio" value="yes" />'.__('Yes', 'shrsb').'</label> &nbsp; <label><input name="shrsbresetallwarn-choice" id="shrsbresetallwarn-cancel" type="radio" value="cancel" />'.__('Cancel', 'shrsb').'</label>
				</form>
			</div>
		</div>';
	}

	//Reset all options to default settings if user clicks the reset button
	if($_POST['shrsbresetallwarn-choice'] == "yes") { //check for reset button click

		$shrsb_recommendations = shrsb_recommendations_set_options('reset');
        
		//delete_option('SHRSB_CustomSprite');
		echo '
		<div id="statmessage" class="shrsb-success">
			<div class="dialog-left fugue f-success">
				'.__('All settings have been reset to their default values.', 'shrsb').'
			</div>
			<div class="dialog-right">
				<img src="'.SHRSB_PLUGPATH.'images/success-delete.jpg" class="del-x" alt=""/>
			</div>
		</div>';
	}

	// processing form submission
	$status_message = "";
	$error_message = "";
	$setting_changed = false;
	
	if(isset($_POST['save_changes_rd'])) {

    // Set success message
    $status_message = __('Your changes have been saved successfully!', 'shrsb');
    $_POST['pageorpost'] = shrsb_set_content_type();
    foreach (array(
        'recommendations', 'num', 'pageorpost','style'
    )as $field) {
        if(isset($_POST[$field])) { // this is to prevent warning if $_POST[$field] is not defined
			    $fieldval = $_POST[$field];
			    if($field == 'recommendations' && $fieldval != $shrsb_recommendations[$field]) {
			      $setting_changed = true;
			    }
            $shrsb_recommendations[$field] = $fieldval;
        } else {
            $shrsb_recommendations[$field] = NULL;
        }
    }
		
		if($shrsb_recommendations['style']=='text')
			$shrsb_recommendations['num']=5;

    update_option('ShareaholicRecommendations',$shrsb_recommendations);
    
    if ($setting_changed == true){
      shr_sendTrackingEvent('FeatureToggle', array('f_updated' => 'f_rec', 'enabled' => ($shrsb_recommendations['recommendations'] == '1' ? 'true' : 'false')));
      shr_recommendationsStatus();
    }
      
  }//Closed Save

	//if there was an error, construct error messages
	if ($error_message != '') {
		echo '
		<div id="errmessage" class="shrsb-error">
			<div class="dialog-left fugue f-error">
				'.$error_message.'
			</div>
			<div class="dialog-right">
				<img src="'.SHRSB_PLUGPATH.'images/error-delete.jpg" class="del-x" alt=""/>
			</div>
		</div>';
	} elseif ($status_message != '') {
		echo '<style type="text/css">#update_sb{display:none !important;}</style>
		<div id="statmessage" class="shrsb-success">
			<div class="dialog-left fugue f-success">
				'.$status_message.'
			</div>
			<div class="dialog-right">
				<img src="'.SHRSB_PLUGPATH.'images/success-delete.jpg" class="del-x" alt=""/>
			</div>
		</div>';
	}
?>

<form name="sexy-bookmarks" id="sexy-bookmarks" action="" method="post">
    <div id="shrsb-col-left" style="width:100%">
		<ul id="shrsb-sortables">

		   <?php if (shrsb_get_current_user_role()=="Administrator"){ ?>
	
          <li>
            <div class="box-mid-head">
                <h2><img src="<?php echo SHRSB_PLUGPATH; ?>/images/thumbs-icon.png" style="float:left;margin-top:2px;margin-right:10px;" alt="Recommendations" /> <?php _e('Recommendations', 'shrsb'); ?></h2>
            </div>
            <div class="box-mid-body" id="toggle5">

                <div class="padding">
                    <div id="genopts">
                        <table><tbody>
                                <tr class="alert-success">
                                    <td><span class="shrsb_option"><?php _e('Enable Recommendations', 'shrsb'); ?> </span></td>
                                    <td WIDTH="120"><label><input <?php echo ((@$shrsb_recommendations['recommendations'] == "1")? 'checked="checked"' : ""); ?> name="recommendations" id="recommendations-yes" type="radio" value="1" /> <?php _e('Yes', 'shrsb'); ?></label></td>
                                    <td WIDTH="120"><label><input <?php echo ((@$shrsb_recommendations['recommendations'] == "0")? 'checked="checked"' : ""); ?> name="recommendations" id="recommendations-no" type="radio" value="0" /> <?php _e('No', 'shrsb'); ?></label></td>                                    
                                </tr>
																
																<tr class="recommendations_prefs-1" style="display:none">
                                            <td><label class="tab" for="style" style="margin-top:7px;"><?php _e('Display thumbnails for each recommendation? </br>(If most posts on your blog don\'t include images, you should set this to \'No\'):', 'shrsb'); ?></label></td>
                                            <td WIDTH="120"><label><input <?php echo ((@$shrsb_recommendations['style'] == "image")? 'checked="checked"' : ""); ?> name="style" id="recommendations-style-image" type="radio" value="image" /> <?php _e('Yes', 'shrsb'); ?></label></td>
                                            <td WIDTH="120"><label><input <?php echo ((@$shrsb_recommendations['style'] == "text")? 'checked="checked"' : ""); ?> name="style" id="recommendations-style-text" type="radio" value="text" /> <?php _e('No', 'shrsb'); ?></label></td>
<!--                                            <td colspan="2"><input style="margin-top:7px;" type="text" id="num" name="num" size="35" placeholder="ex. UA-XXXXXXXX-X" value="<?php echo @$shrsb_recommendations['style']; ?>" /></td>-->

                                </tr>		

                                <tr class="recommendations_prefs-2" style="display:none;">
																	<td><label class="tab" for="num" style="margin-top:7px;"><?php _e('Number of recommendations displayed:', 'shrsb'); ?></label></td>
                                  <td WIDTH="120"><label><input <?php echo ((@$shrsb_recommendations['num'] == "3")? 'checked="checked"' : ""); ?> name="num" id="recommendations-yes" type="radio" value="3" /> <?php _e('3', 'shrsb'); ?></label></td>
                                  <td WIDTH="120"><label><input <?php echo ((@$shrsb_recommendations['num'] == "4")? 'checked="checked"' : ""); ?> name="num" id="recommendations-no" type="radio" value="4" /> <?php _e('4', 'shrsb'); ?></label></td>
                                </tr>
                                
																<tr>
                                	<td colspan="3"><br /><p>Once enabled, we will analyze your content and begin generating recommended posts to display. This may take up to several hours if you are a new Shareaholic user and depending on the number of posts on your blog. The quality of recommended stories will improve once we complete our crawl of your website.</p><p><span class="label label-info">Tip</span> we recommend using Shareaholic sharing tools as they help boost the quality of your recommendations.</p></td>
																</tr>
                        </tbody></table>
                    </div>
                </div>
            </li>
            <li>
                <div class="box-mid-head">
                  <h2 class="fugue f-footer"><?php _e('Recommendations Placement', 'shrsb'); ?></h2>
                </div>
                <div class="box-mid-body" id="toggle5">
                  <div class="padding">

                    <?php shrsb_options_menu_type(@$shrsb_recommendations['pageorpost']); ?>

                    <br />
                  </div>
              </div>
            </li>

		<?php } ?>
				
		</ul>
		
		<?php if (shrsb_get_current_user_role()=="Administrator"){ ?>
			
			<div style="clear:both;"></div>
			<input type="hidden" name="save_changes_rd" value="1" />
        	<div class="shrsbsubmit"><input type="submit" id="save_changes_rd" value="<?php _e('Save Changes', 'shrsb'); ?>" /></div>
		</form>
		<form action="" method="post">
			<input type="hidden" name="reset_all_options_recommendations" id="reset_all_options_recommendations" value="0" />
			<!-- <div class="shrsbreset"><input type="submit" value="<?php _e('Reset Settings', 'shrsb'); ?>" /></div> -->
		</form>
		
	<?php } ?>	

	<?php echo shrsb_getfooter(); ?>
	
</div>

<?php

//Right Side helpful links
echo shrsb_right_side_menu();
//Snap Engage
echo get_snapengage();

}//closing brace for function "shrsb_settings_page"

?>