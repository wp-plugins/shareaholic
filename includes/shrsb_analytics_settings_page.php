<?php

/* 
 * @desc Topbar Settings page
*/

function shrsb_analytics_settings_page() {
	global $shrsb_analytics;
    // Add all the global varaible declarations for the $shrsb_tb_plugopts
	echo '<div class="wrap""><div class="icon32" id="icon-options-general"><br></div><h2>'.__('Social Analytics Settings', 'shrsb').'</h2></div>';
    //Defaults - set if not present
    if (!isset($_POST['reset_all_options_analytics'])){$_POST['reset_all_options_analytics'] = '1';}
    if (!isset($_POST['shrsbresetallwarn-choice'])){$_POST['shrsbresetallwarn-choice'] = 'no';}
    
	if($_POST['reset_all_options_analytics'] == '0') {
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

		$shrsb_analytics = shrsb_analytics_set_options('reset');
        
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
	
	if(isset($_POST['save_changes_sa'])) {

    // Set success message
    $status_message = __('Your changes have been saved successfully!', 'shrsb');

    foreach (array(
        'pubGaSocial', 'pubGaKey'
    )as $field) {
        if(isset($_POST[$field])) { // this is to prevent warning if $_POST[$field] is not defined
			$fieldval = $_POST[$field];
			if($field == 'pubGaSocial' && $fieldval != $shrsb_analytics[$field]) {
			  $setting_changed = true;
			}
            $shrsb_analytics[$field] = $fieldval;
        } else {
            $shrsb_analytics[$field] = NULL;
        }
    }

    update_option('ShareaholicAnalytics',$shrsb_analytics);
    
    if ($setting_changed == true){
      shr_sendTrackingEvent('FeatureToggle', array('f_updated' => 'f_analytics', 'enabled' => ($shrsb_analytics['pubGaSocial'] == '1' ? 'true' : 'false')));
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

		<li>
                <div class="box-mid-head">
                    <h2 class="fugue f-status"><?php _e('Shareaholic Social Analytics - Grow Your Traffic and Referrals', 'shrsb'); ?></h2>
                </div>
				<div class="box-mid-body">
                        <div style="padding:8px;background:#FDF6E5;"><img src="<?php echo SHRSB_PLUGPATH; ?>images/chart.png" align="right" alt="New!" />
                                <?php

									$parse = parse_url(get_bloginfo('url'));
                                    $share_url = "https://www.shareaholic.com/api/data/".$parse['host']."/sharecount/30";
                                    $top_users_url =  "https://www.shareaholic.com/api/data/".$parse['host']."/topusers/16/";

                                    echo sprintf(__('<b style="font-size:14px;line-height:22px;">Did you know that content from this website has been shared <span style="color:#CC1100;"><span id="bonusShareCount"></span> time(s)</span> in the past <span id="bonusShareTimeFrame"></span> day(s)?</b>', 'shrsb'));
                                ?>

                                <script type ="text/javascript">
                                    (function($){
                                        $(document).ready( function () {
                                            var url = <?php echo "'".$share_url."'";?>;
                                            var top_users_url  = <?php echo "'".$top_users_url."'";?>;
                                            $.getJSON(url+'?callback=?', function (obj) {
                                                $('#bonusShareCount').text(obj.sharecount);
                                                $('#bonusShareTimeFrame').text(obj.timeframe);
                                            });

                                            $.getJSON(top_users_url+'?callback=?', function (obj) {
                                                add_faces(obj);
                                            });
                                        });

                                        var add_faces = function(obj) {
                                            if(obj && obj.length) {
                                                var shuffle = function(v){
                                                    //+ Jonas Raoni Soares Silva
                                                    //@ http://jsfromhell.com/array/shuffle [rev. #1]
                                                    for(var j, x, i = v.length; i; j = parseInt(Math.random() * i), x = v[--i], v[i] = v[j], v[j] = x);
                                                    return v;
                                                };
                                                obj = shuffle(obj);

                                                $('#bonusShareTopUser').show();
                                                var face_ul = $('<ul id="bonusShareFacesUL"/>');
                                                for(var i=0; i<obj.length; ++i) {
                                                    var shr_profile_url = "https://shareaholic.com/" + obj[i].username;
                                                    face_ul.append(
                                                        $("<li class='bonusShareLi'>").append("<a target='_blank' href="+shr_profile_url+"><img class='bonusShareFaces' title=" + obj[i].username + " src=" + obj[i].picture_url + "></img></a>")
                                                    );
                                                }

                                                $('#bonusShareTopUser').append(face_ul);

                                            }
                                        };
                                    })(jQuery);
                                </script>
                                <br/><br/>
                                <div id="bonusShareTopUser" style="display:none"><b><?php _e('Meet who spreads your content the most:', 'shrsb'); ?></b></div>

                                <br />
                                <div style="background: url(https://shareaholic.com/media/images/border_hr.png) repeat-x scroll left top; height: 2px;"></div>
                                <br />
                                  <?php  echo sprintf(__('<span style="font-size: 12px;">Shareaholic reports all of your important social media metrics including popular pages on your website, referral channels, and who are making referrals and spreading your webpages on the internet on your behalf bringing you back more traffic and new visitors for free.</span> <br><br> <b><span style="color:#CC1100;">What are you waiting for?</span> You can access detailed %ssocial engagement analytics%s about your website right now.</b>', 'shrsb'), '<a href="https://shareaholic.com/publishers/analytics/'.$parse['host'].'/">', '</a>');
                                ?>

                        </div>
                </div>
            </li>


       <?php if (shrsb_get_current_user_role()=="Administrator"){ ?>
	
          <li>
            <div class="box-mid-head">
                <h2><img src="<?php echo SHRSB_PLUGPATH; ?>/images/ga-icon.png" style="float:left;margin-top:2px;margin-right:10px;" alt="Google Analytics" /> <?php _e('Google Analytics', 'shrsb'); ?></h2>
            </div>
            <div class="box-mid-body" id="toggle5">

                <div class="padding">
                    <div id="genopts">
                        <table>
                          <tbody>
                                <tr>
                                    <td><span class="shrsb_option"><?php _e('Enable Google Analytics Social Tracking', 'shrsb'); ?> (<a href="http://code.google.com/apis/analytics/docs/tracking/gaTrackingSocial.html" target="_blank">?</a>)</span></td>
                                    <td WIDTH="120"><label><input <?php echo ((@$shrsb_analytics['pubGaSocial'] == "1")? 'checked="checked"' : ""); ?> name="pubGaSocial" id="pubGaSocial-yes" type="radio" value="1" /> <?php _e('Yes', 'shrsb'); ?></label></td>
                                    <td WIDTH="120"><label><input <?php echo ((@$shrsb_analytics['pubGaSocial'] == "0")? 'checked="checked"' : ""); ?> name="pubGaSocial" id="pubGaSocial-no" type="radio" value="0" /> <?php _e('No', 'shrsb'); ?></label></td>
                                </tr>

                                <tr class="pubGaSocial_prefs" style="display:none;">
                                    <td><label class="tab" for="pubGaKey" style="margin-top:7px;"><?php _e('Your Google Analytics Property ID:', 'shrsb'); ?></label></td>
                                    <td colspan="2"><input style="margin-top:7px;" type="text" id="pubGaKey" name="pubGaKey" size="35" placeholder="ex. UA-XXXXXXXX-X" value="<?php echo @$shrsb_analytics['pubGaKey']; ?>" /></td>
                                </tr>
                                
                        </tbody>
                      </table>
                    </div>
                </div>
            </li>

		<?php } ?>
				
		</ul>
		
		<?php if (shrsb_get_current_user_role()=="Administrator"){ ?>
			
			<div style="clear:both;"></div>
			<input type="hidden" name="save_changes_sa" value="1" />
        	<div class="shrsbsubmit"><input type="submit" id="save_changes_sa" value="<?php _e('Save Changes', 'shrsb'); ?>" /></div>
		</form>
		<form action="" method="post">
			<input type="hidden" name="reset_all_options_analytics" id="reset_all_options_analytics" value="0" />
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


// Old analytics Page
function shrsb_analytics_page() {
?>
    <h2 class="shrsblogo"><span class="sh-logo"></span></h2>

    <div id="shrsb-col-left" style="width:100%;">

        <ul id="shrsb-sortables">
            <li>
                <div class="box-mid-head">
                    <h2 class="fugue f-status"><?php _e('Shareaholic Analtyics', 'shrsb'); ?></h2>
                </div>
                <div class="box-mid-body">
                      <div class="padding">
                        <div style="position:relative;width:80%;">
                            <p><strong>
                                <?php _e('Shareaholic Analtyics is coming soon!', 'shrsb'); ?>
                                </strong>
                                <br><br>
                                <?php _e('Register your account today to recieve update info via email.', 'shrsb'); ?>
                                <div class="shrsbsubmit">
                                    <input type="button" onclick ="window.open('https://shareaholic.com/publishers_apps/new_publishers_app')" value="<?php _e('Get Share Pro', 'shrsb'); ?>" />
                                </div>
                            </p>
                        </div>
                      </div>
                </div>
            </li>
        </ul>
    </div>
<?php
    }
?>