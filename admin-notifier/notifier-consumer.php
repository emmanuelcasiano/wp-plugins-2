<?php

/**
 * Plugin Name: Notifier Consumer
 * Description: Ch.02 side project — consuming a custom hook from another plugin.
 * Version:     1.0.0
 * Text Domain: notifier-consumer
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Hook into Admin Notifier's custom action.
 * Note: this only works if Admin Notifier is also active.
 * In real plugins, you'd check if the hook exists first.
 */
add_action('an_admin_logged_in', 'nc_handle_admin_login', 10, 1);

function nc_handle_admin_login($context)
{
    error_log(sprintf(
        '[Notifier Consumer] Admin "%s" (ID: %d) logged in at %s from IP %s',
        $context['user_login'],
        $context['user_id'],
        $context['login_time'],
        $context['ip_address']
    ));

    // Store the last admin login time as a site option
    update_option('nc_last_admin_login', $context, false);
}

/**
 * Display the last admin login in the dashboard.
 * Using the 'dashboard_glance_items' filter to add a line to "At a Glance" widget.
 */
add_filter('dashboard_glance_items', 'nc_add_glance_item');

function nc_add_glance_item($items)
{
    $last = get_option('nc_last_admin_login');

    if (! $last) {
        $items[] = 'No admin logins recorded yet.';
        return $items;
    }

    $items[] = sprintf(
        'Last admin login: <strong>%s</strong> at %s',
        esc_html($last['user_login']),
        esc_html($last['login_time'])
    );

    return $items; // Don't forget to return!
}
