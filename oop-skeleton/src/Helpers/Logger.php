<?php

namespace OopSkeleton\Helpers;

/**
 * Simple logger wrapper.
 * Decoupled from WordPress — easy to test or swap out.
 */
class Logger
{

    /**
     * Log a message to debug.log (when WP_DEBUG_LOG is true).
     *
     * @param string $message  Human-readable message.
     * @param mixed  $context  Optional data to dump alongside (array, object, etc).
     */
    public function log(string $message, mixed $context = null): void
    {
        if (! defined('WP_DEBUG_LOG') || ! WP_DEBUG_LOG) {
            return;
        }

        $entry = '[OopSkeleton] ' . $message;

        if (null !== $context) {
            $entry .= ' | context: ' . wp_json_encode($context);
        }

        error_log($entry);
    }

    /**
     * Log only in debug mode — stripped in production.
     */
    public function debug(string $message, mixed $context = null): void
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $this->log('[DEBUG] ' . $message, $context);
        }
    }
}
