<div class='wrap'>
  <div id="icon-options-general" class="icon32"></div>
  <h2><?php echo sprintf(__('Shareaholic: Advanced Settings', 'shareaholic')); ?></h2>
  <div style="margin-top:20px;"></div>
  <?php echo sprintf(__('You rarely should need to edit the settings on this page.', 'shareaholic')); ?>
  <div style="min-height:300px;">
    <form name='advanced_settings' method='post' action='<?php echo $action ?>'>
    <input type='hidden' name='already_submitted' value='Y'>
      <div class='clear'>
        <fieldset>
          <input type='checkbox' id='tracking' name='shareaholic[disable_tracking]' class='check'
            <?php if (isset($settings['disable_tracking'])) { ?>
              <?php echo ($settings['disable_tracking'] == 'on' ? 'checked' : '') ?>
              <?php } ?>>
            <label style="display: inline-block;" for="tracking"><?php echo sprintf(__('Disable Analytics', 'shareaholic')); ?> <?php echo sprintf(__('(it is recommended NOT to disable analytics)', 'shareaholic')); ?></label>
          <br />
          <input type='checkbox' id='og_tags' name='shareaholic[disable_og_tags]' class='check'
            <?php if (isset($settings['disable_og_tags'])) { ?>
              <?php echo ($settings['disable_og_tags'] == 'on' ? 'checked' : '') ?>
              <?php } ?>>
            <label style="display: inline-block;" for="og_tags"><?php echo sprintf(__('Do not automatically include <code>Open Graph</code> tags', 'shareaholic')); ?> <?php echo sprintf(__('(it is recommended NOT to disable open graph tags)', 'shareaholic')); ?></label>
        </fieldset>
      </div>

      <div class='clear' style="padding-top:20px; padding-bottom:35px;">
        <input type='submit' value='<?php echo sprintf(__('Save Changes', 'shareaholic')); ?>'>
      </div>
    </form>

    <form name='reset_settings' method='post' action='<?php echo $action ?>'>
      <input type='hidden' name='reset_settings' value='Y'>
      <fieldset>
        <p>Clicking this button will reset all of your settings and start you from scratch.</p>
        <input type='submit' value='<?php echo sprintf(__('Reset Everything', 'shareaholic')); ?>'>
      </fieldset>
    </form>
  </div>
</div>
<?php ShareaholicAdmin::show_footer(); ?>
<?php ShareaholicAdmin::include_snapengage(); ?>
