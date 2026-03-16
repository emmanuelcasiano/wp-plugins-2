<?php

namespace OopSkeleton\Services;

use OopSkeleton\Helpers\Logger;

/**
 * Adds a custom "Reading Level" meta box to Posts.
 * Demonstrates: meta boxes, nonces, sanitization, save_post hook.
 * (Security patterns are previewed here — Chapter 04 covers them in depth.)
 */
class PostMetaService
{

    private const META_KEY   = '_opskel_reading_level';
    private const NONCE_KEY  = 'opskel_meta_nonce';
    private const NONCE_ACTION = 'opskel_save_meta';

    public function __construct(
        private readonly Logger $logger
    ) {}

    public function register(): void
    {
        add_action('add_meta_boxes', [$this, 'register_meta_box']);
        add_action('save_post',      [$this, 'save_meta'], 10, 2);
    }

    // ─── Hook callbacks ──────────────────────────────────────────────────────

    public function register_meta_box(): void
    {
        add_meta_box(
            'opskel-reading-level',         // Unique ID
            'Reading Level',                // Box title
            [$this, 'render_meta_box'],   // Callback
            'post',                         // Post type
            'side',                         // Context (side column)
            'default'                       // Priority
        );
    }

    public function render_meta_box(\WP_Post $post): void
    {
        // Get current value (empty string if not set)
        $current_level = $this->get_reading_level($post->ID);

        // Pass data to view — no logic in the template
        require OPSKEL_PATH . 'src/Views/meta-box.php';
    }

    public function save_meta(int $post_id, \WP_Post $post): void
    {
        // Bail on autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Bail on wrong post type
        if ('post' !== $post->post_type) {
            return;
        }

        // Verify nonce — prevents CSRF (full explanation in Ch.04)
        $nonce = $_POST[self::NONCE_KEY] ?? '';
        if (! wp_verify_nonce($nonce, self::NONCE_ACTION)) {
            return;
        }

        // Check user capability
        if (! current_user_can('edit_post', $post_id)) {
            return;
        }

        // Sanitize before saving — never trust raw input
        $level = sanitize_text_field($_POST['opskel_reading_level'] ?? '');

        $this->set_reading_level($post_id, $level);

        $this->logger->debug('Meta saved for post', [
            'post_id' => $post_id,
            'level'   => $level,
        ]);
    }

    // ─── Private data methods ────────────────────────────────────────────────

    private function get_reading_level(int $post_id): string
    {
        return (string) get_post_meta($post_id, self::META_KEY, true);
    }

    private function set_reading_level(int $post_id, string $level): void
    {
        if ('' === $level) {
            delete_post_meta($post_id, self::META_KEY);
            return;
        }
        update_post_meta($post_id, self::META_KEY, $level);
    }
}
