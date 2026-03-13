<?php

/**
 * Plugin Name: Lifecycle Logger
 * Description: Logs every major WP hook to debug.log so you can see the sequence live.
 * Version:     1.0.0
 * Text Domain: lifecycle-logger
 */

if (! defined('ABSPATH')) {
    exit;
}

// ─── The hooks we want to observe, in rough execution order ─────────────────
$hooks_to_log = [
    'muplugins_loaded',
    'plugins_loaded',
    'setup_theme',
    'after_setup_theme',
    'init',
    'wp_loaded',
    'parse_request',
    'send_headers',
    'wp',
    'template_redirect',
    'wp_head',
    'wp_footer',
    'shutdown',
];

foreach ($hooks_to_log as $hook) {
    // We use a closure to capture $hook in the loop
    add_action($hook, function () use ($hook) {
        $memory = round(memory_get_usage() / 1024 / 1024, 2);
        error_log(sprintf(
            '[Lifecycle] %-25s | queries: %d | memory: %s MB | time: %ss',
            $hook,
            get_num_queries(),
            $memory,
            timer_stop(0, 4)
        ));
    }, 999); // Priority 999 = run after everything else on this hook
}
