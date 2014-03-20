<!-- This site is powered by Shareaholic - https://shareaholic.com -->
<script type='text/javascript' data-cfasync='false'>
  //<![CDATA[
    (function() {
      var shr = document.createElement('script');
      shr.setAttribute('data-cfasync', 'false');
      shr.src = '<?php echo ShareaholicUtilities::asset_url('pub/shareaholic.js') ?>';
      shr.type = 'text/javascript'; shr.async = 'true';
      shr.onload = shr.onreadystatechange = function() {
        var rs = this.readyState;
        if (rs && rs != 'complete' && rs != 'loaded') return;
        var site_id = '<?php echo $api_key; ?>';
        try { Shareaholic.init(site_id); } catch (e) {}
      };
      var s = document.getElementsByTagName('script')[0];
      s.parentNode.insertBefore(shr, s);
    })();
  //]]>
</script>
