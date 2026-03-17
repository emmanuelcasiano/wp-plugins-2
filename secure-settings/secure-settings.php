<?php

/**
 * Plugin Name: Secure Settings
 * Description: Ch.04 side project — production-quality settings page.
 * Version:     1.0.0
 * Requires PHP: 8.1
 * Text Domain: secure-settings
 */

if (! defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

add_action('plugins_loaded', function () {
    SecureSettings\Plugin::get_instance()->boot();
});
