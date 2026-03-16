<?php

/**
 * Plugin Name: OOP Skeleton
 * Description: Ch.03 — reusable OOP plugin scaffold.
 * Version:     1.0.0
 * Requires PHP: 8.1
 * Text Domain: oop-skeleton
 */

if (! defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

// Boot the plugin — this is the ONLY logic in this file
add_action('plugins_loaded', function () {
    OopSkeleton\Plugin::get_instance()->boot();
});
