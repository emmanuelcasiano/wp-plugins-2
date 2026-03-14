<?php

/**
 * Plugin Name: Content Modifier
 * Description: Ch.02 side project — chaining filters and understanding priority.
 * Version:     1.0.0
 * Text Domain: content-modifier
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Filter 1 — runs at priority 5 (before default 10)
 * Wraps the content in a container div
 */
add_filter('the_content', 'cm_wrap_content', 5);

function cm_wrap_content($content)
{
    if (! is_singular()) {
        return $content; // Only on single post/page views
    }
    return '<div class="cm-wrapper" style="background-color: #fcd9d9">' . $content . '</div>';
}

/**
 * Filter 2 — runs at priority 10 (default)
 * Appends a "reading time" estimate
 */
add_filter('the_content', 'cm_add_reading_time', 10);

function cm_add_reading_time($content)
{
    if (! is_singular()) {
        return $content;
    }

    // Strip tags and count words for an estimate
    $word_count   = str_word_count(wp_strip_all_tags($content));
    $reading_time = max(1, (int) ceil($word_count / 200)); // ~200 wpm

    $notice = sprintf(
        '<p class="cm-reading-time"><em>Reading time: %d min</em></p>',
        $reading_time
    );

    return $content . $notice;
}

/**
 * Filter 3 — runs at priority 20 (after the others)
 * Appends a custom call-to-action
 * This sees content already modified by filters 1 and 2
 */
add_filter('the_content', 'cm_add_cta', 20);

function cm_add_cta($content)
{
    if (! is_singular('post')) {
        return $content; // Only on blog posts, not pages
    }

    $cta = '<div class="cm-cta" style="background:#f0f4ff;padding:16px;margin-top:20px;border-radius:6px;">'
        . '<strong>Enjoyed this post?</strong> Subscribe for more.☠️'
        . '</div>';

    return $content . $cta;
}

/**
 * EXPERIMENT: Try breaking this intentionally.
 * Uncomment the filter below — notice the content disappears on the page.
 * This is the "forgot to return" bug in action.
 */
add_filter('the_content', 'cm_broken_filter', 15);
function cm_broken_filter($content)
{
    return $content . ' [modified]'; // BUG: no return statement
}
