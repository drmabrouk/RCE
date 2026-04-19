<?php
/**
 * Footer Template
 * Removed system footer bar as per requirements.
 */
?>
        </div> <!-- .control-content-inner -->
    </main> <!-- .control-main-content -->
</div> <!-- .control-dashboard -->

<?php include_once CONTROL_PATH . 'templates/users.php'; ?>
<?php include_once CONTROL_PATH . 'templates/roles.php'; ?>
<?php include_once CONTROL_PATH . 'templates/settings.php'; ?>

<script>
jQuery(document).ready(function($) {
    // Dynamic Height Adjustment for Sidebar
    function adjustSidebar() {
        if ($(window).width() > 991) {
            $('.control-sidebar').css('height', $(window).height());
        }
    }
    $(window).resize(adjustSidebar);
    adjustSidebar();
});
</script>
