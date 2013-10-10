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
        var apikey = '<?php echo $api_key; ?>';
        try { Shareaholic.init(apikey); } catch (e) {}
      };
      var s = document.getElementsByTagName('script')[0];
      s.parentNode.insertBefore(shr, s);
    })();
  //]]>
</script>
