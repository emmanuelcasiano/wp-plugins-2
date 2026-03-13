<?php
// Prevent direct access
if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Clean up everything our plugin stored — leave no trace
delete_option('hw_activated_at');
