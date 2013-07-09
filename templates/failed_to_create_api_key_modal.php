<div class='reveal-modal blocking-modal' id='failed_to_create_api_key'>
  <h4><?php echo sprintf(__('Setup Shareaholic', 'shareaholic')); ?></h4>
  <div class="content pal">
  <div class="line pvl">
    <div class="unit size3of3">
      <p>
        <?php echo sprintf(__('It appears that we are having some trouble setting up Shareaholic for WordPress right now. This is usually temporary. Please revisit this section after a few minutes or click "retry" now.', 'shareaholic')); ?>
      </p>
    </div>
  </div>
  <div class="pvl">
    <a id='get_started' class="btn_main" href=''><?php echo sprintf(__('Retry', 'shareaholic')); ?></a>
    <br /><br />
    <a href='<?php echo admin_url() ?>' style="font-size:12px; font-weight:normal;"><?php echo sprintf(__('or, try again later', 'shareaholic')); ?></a>
  </div>
  </div>
</div>
