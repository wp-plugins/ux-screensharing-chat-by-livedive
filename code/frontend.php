<!-- LIVEDIVE SCRIPTS -->
<script src="//get.livedive.co/livedive.js" async defer></script> 
<script> 
  window.LiveDive=function(X){X.siteid='<?php echo $site_id ?>',X.ready=
  X.ready||{};var e=X.ready.onReady=[];return X.ready.ready=!1,X.set=X.do=function(d,n,a){
  X.ready.ready?X.ready.dofn({f:d,a:n,o:a}):e.push({f:d,a:n,o:a})},
  X.setOnce=function(e,d,n){n=n||{},n.once=!0,X.set(e,d,n)},X}(window.LiveDive||{});
</script>