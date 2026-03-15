<?php

/**
 * Plugin Name: Admin Notifier
 * Description: Ch.02 side project — creating and firing custom action hooks.
 * Version:     1.0.0
 * Text Domain: admin-notifier
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Fire a custom action whenever an admin logs in.
 * We hook into 'wp_login' which passes ($user_login, $user).
 */
add_action('wp_login', 'an_on_login', 10, 2);

function an_on_login($user_login, $user)
{
    // Only care about admins
    if (! in_array('administrator', (array) $user->roles, true)) {
        return;
    }

    $context = [
        'user_id'    => $user->ID,
        'user_login' => $user_login,
        'user_email' => $user->user_email,
        'login_time' => current_time('mysql'),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    ];

    /**
     * Fire our custom action — any code can now hook into this.
     * Following WordPress convention: prefix + descriptive name.
     */
    do_action('an_admin_logged_in', $context);

    error_log('[Admin Notifier] Custom hook fired for user: ' . $user_login);
}
