<div class='wrap'>
  <div id="icon-options-general" class="icon32"></div>
  <h2><?php echo sprintf(__('Shareaholic: Advanced Settings', 'shareaholic')); ?></h2>
  <div style="margin-top:20px;"></div>
  <?php echo sprintf(__('You rarely need to edit these settings. We recommend keeping with the default. ', 'shareaholic')); ?>
  <div style="min-height:300px;">
    <form name='advanced_settings' method='post' action='<?php echo $action ?>'>
    <input type='hidden' name='already_submitted' value='Y'>
      <div class='clear'>
        <fieldset>
          <input type='checkbox' id='tracking' name='shareaholic[disable_tracking]' class='check'
            <?php if (isset($settings['disable_tracking'])) { ?>
              <?php echo ($settings['disable_tracking'] == 'on' ? 'checked' : '') ?>
              <?php } ?>>
            <label style="display: inline-block;" for="tracking"><?php echo sprintf(__('Disable Analytics', 'shareaholic')); ?> <?php echo sprintf(__('(not recommended)', 'shareaholic')); ?></label>
          <br />
          <input type='checkbox' id='og_tags' name='shareaholic[disable_og_tags]' class='check'
            <?php if (isset($settings['disable_og_tags'])) { ?>
              <?php echo ($settings['disable_og_tags'] == 'on' ? 'checked' : '') ?>
              <?php } ?>>
            <label style="display: inline-block;" for="og_tags"><?php echo sprintf(__('Do not automatically include <code>Open Graph</code> tags', 'shareaholic')); ?> <?php echo sprintf(__('(not recommended)', 'shareaholic')); ?></label>

            <br />

        </fieldset>
      </div>

      <div class='clear' style="padding-top:20px; padding-bottom:35px;">
        <input type='submit' value='<?php echo sprintf(__('Save Changes', 'shareaholic')); ?>'>
      </div>
    </form>
  </div>
</div>
<?php ShareaholicAdmin::include_snapengage(); ?>
<?php ShareaholicAdmin::show_footer(); ?>
