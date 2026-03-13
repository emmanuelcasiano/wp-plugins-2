<?php

/**
 * Plugin Name: Hello World
 * Description: Chapter 01 side project — understanding the plugin skeleton.
 * Version:     1.0.0
 * Author:      Emmanuel Casiano
 * Text Domain: hello-world
 */

// Prevent direct file access — always do this
if (! defined('ABSPATH')) {
    exit;
}

// ─── Activation hook ────────────────────────────────────────────────────────
// Fires once when the plugin is activated in the admin.
// Good for: creating DB tables, setting default options, flushing rewrite rules.
register_activation_hook(__FILE__, 'hw_activate');

function hw_activate()
{
    // We'll store a timestamp so we can see this ran
    add_option('hw_activated_at', current_time('mysql'));
    error_log('[Hello World] Plugin activated at ' . current_time('mysql'));
}

// ─── Deactivation hook ───────────────────────────────────────────────────────
// Fires when plugin is deactivated. Does NOT delete data — that's uninstall.php.
// Good for: clearing scheduled events, flushing rewrite rules.
register_deactivation_hook(__FILE__, 'hw_deactivate');

function hw_deactivate()
{
    error_log('[Hello World] Plugin deactivated.');
}

// ─── Your first action hook ──────────────────────────────────────────────────
// We hook into 'init' — fires on every request after plugins are loaded.
add_action('init', 'hw_on_init');

function hw_on_init()
{
    error_log('[Hello World] init hook fired. Current user ID: ' . get_current_user_id());
}

// ─── Your first filter hook ──────────────────────────────────────────────────
// We filter 'the_title' to append a badge to every post title.
add_filter('the_title', 'hw_modify_title');

function hw_modify_title($title)
{
    // Only on the front-end, only inside the loop
    if (is_admin() || ! in_the_loop()) {
        return $title;
    }
    return $title . ' 👋🔥';
}
