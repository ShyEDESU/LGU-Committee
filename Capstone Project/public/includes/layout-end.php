<?php
// Note: module-content-wrapper should be manually closed in the page file 
// before including the footer to ensure proper spacing and layout.
?>
</main>
</div><!-- /.flex-1.flex.flex-col -->
</div><!-- /.flex.h-screen -->

<!-- Template Scripts -->
<script src="<?php echo $footerPathPrefix; ?>assets/js/script-updated.js"></script>

<!-- Unified Session Management -->
<script src="<?php echo $footerPathPrefix; ?>assets/js/session-manager.js"></script>

<script>
    // System-wide global clock sync or other footer-specific JS
    console.log('CMS Footer Initialized');
</script>
</body>

</html>