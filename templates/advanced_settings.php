<div class='wrap'>
  <div id="icon-options-general" class="icon32"></div>
  <h2><?php echo sprintf(__('Shareaholic: Advanced Settings', 'shareaholic')); ?></h2>
  <div style="margin-top:20px;"></div>
    
  <div class='unit size4of5' style="min-height:300px;">
    
    <span class="helper"><i class="icon-star"></i> <?php echo sprintf(__('You rarely should need to edit the settings on this page.', 'shareaholic')); ?> <?php echo sprintf(__('After changing any Shareaholic advanced setting, it is good practice to clear any WordPress caching plugins (if you are using one).', 'shareaholic')); ?></p></span>

    <form name='advanced_settings' method='post' action='<?php echo $action ?>'>
    <?php wp_nonce_field($action, 'nonce_field') ?>
    <input type='hidden' name='already_submitted' value='Y'>
      <div class='clear'>
        <fieldset class="app">
        <legend><h2><?php echo sprintf(__('Advanced', 'shareaholic')); ?></h2></legend>
          <input type='checkbox' id='tracking' name='shareaholic[disable_tracking]' class='check'
            <?php if (isset($settings['disable_tracking'])) { ?>
              <?php echo ($settings['disable_tracking'] == 'on' ? 'checked' : '') ?>
              <?php } ?>>
            <label style="display: inline-block; font-size:12px;" for="tracking"><?php echo sprintf(__('Disable Analytics', 'shareaholic')); ?> <?php echo sprintf(__('(it is recommended NOT to disable analytics)', 'shareaholic')); ?></label>
          <br />
          <input type='checkbox' id='og_tags' name='shareaholic[disable_og_tags]' class='check'
            <?php if (isset($settings['disable_og_tags'])) { ?>
              <?php echo ($settings['disable_og_tags'] == 'on' ? 'checked' : '') ?>
              <?php } ?>>
            <label style="display: inline-block; font-size:12px;" for="og_tags"><?php echo sprintf(__('Do not automatically include <code>Open Graph</code> tags', 'shareaholic')); ?> <?php echo sprintf(__('(it is recommended NOT to disable open graph tags)', 'shareaholic')); ?></label>
            
          <div class='clear' style="padding-top:10px;"></div>
          <input type='submit' onclick="this.value='<?php echo sprintf(__('Saving Changes...', 'shareaholic')); ?>';" value='<?php echo sprintf(__('Save Changes', 'shareaholic')); ?>'>
        </fieldset>
      </div> 
    </form>
    
    <div class='clear'></div>  
    
    <form name='reset_settings' method='post' action='<?php echo $action ?>'>
      <?php wp_nonce_field($action, 'nonce_field') ?>
      <input type='hidden' name='reset_settings' value='Y'>
      <fieldset class="app">
        <legend><h2><?php echo sprintf(__('Reset', 'shareaholic')); ?></h2></legend>
        <?php echo sprintf(__('This will reset all of your settings and start you from scratch. This can not be undone.', 'shareaholic')); ?>
        <div class='clear'></div>  
        <input type='submit' onclick="this.value='<?php echo sprintf(__('Resetting Plugin...', 'shareaholic')); ?>';" value='<?php echo sprintf(__('Reset Plugin', 'shareaholic')); ?>'>        
      </fieldset>
      
      <div class='clear' style="padding-bottom:35px;"></div>
      
    </form>    
  </div>
</div>
<?php ShareaholicAdmin::show_footer(); ?>
<?php ShareaholicAdmin::include_snapengage(); ?>
