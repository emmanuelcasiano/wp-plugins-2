<?php

namespace SecureSettings\Services;

/**
 * SettingsService — a complete, secure admin settings page.
 *
 * Security patterns demonstrated:
 *   - Capability check on page registration AND on save
 *   - Nonce generated in view, verified in handler
 *   - All input sanitized by data type
 *   - All output escaped by context
 *   - wp_safe_redirect after save (Post/Redirect/Get pattern)
 *   - Settings API for structured option storage
 */
class SettingsService
{

    private const OPTION_KEY    = 'ss_settings';
    private const NONCE_ACTION  = 'ss_save_settings';
    private const NONCE_FIELD   = 'ss_nonce';
    private const MENU_SLUG     = 'secure-settings';
    private const CAPABILITY    = 'manage_options';

    public function register(): void
    {
        add_action('admin_menu',        [$this, 'register_menu']);
        add_action('admin_post_ss_save', [$this, 'handle_save']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    // ─── Menu registration ────────────────────────────────────────────────────

    public function register_menu(): void
    {
        add_options_page(
            'Secure Settings',      // Page title
            'Secure Settings',      // Menu label
            self::CAPABILITY,       // Required capability — WP enforces this
            self::MENU_SLUG,        // Menu slug
            [$this, 'render_page']
        );
    }

    // ─── Asset enqueuing ──────────────────────────────────────────────────────

    public function enqueue_assets(string $hook): void
    {
        // Only load on our page — avoid polluting every admin page
        if ('settings_page_' . self::MENU_SLUG !== $hook) {
            return;
        }
        // Enqueue plugin styles/scripts here when needed
    }

    // ─── Page render ──────────────────────────────────────────────────────────

    public function render_page(): void
    {
        // Double-check capability in the render method too
        // (menu registration checks it, but defense in depth)
        if (! current_user_can(self::CAPABILITY)) {
            wp_die(esc_html__('Insufficient permissions.', 'secure-settings'), 403);
        }

        $settings = $this->get_settings();
        $saved    = isset($_GET['ss_saved']);

        // Load view — pass data as variables, view only does output
        require SS_PATH . 'src/Views/settings-page.php';
    }

    // ─── Save handler ─────────────────────────────────────────────────────────

    public function handle_save(): void
    {

        // Guard 1 — nonce
        if (! wp_verify_nonce($_POST[self::NONCE_FIELD] ?? '', self::NONCE_ACTION)) {
            wp_die(esc_html__('Security check failed.', 'secure-settings'), 403);
        }

        // Guard 2 — capability
        if (! current_user_can(self::CAPABILITY)) {
            wp_die(esc_html__('Insufficient permissions.', 'secure-settings'), 403);
        }

        // Guard 3 — referer (extra layer, optional but good practice)
        check_admin_referer(self::NONCE_ACTION, self::NONCE_FIELD);

        // Sanitize every field by its appropriate type
        $settings = $this->sanitize_settings($_POST);

        // Validate — check for business rule violations
        $errors = $this->validate_settings($settings);

        if (! empty($errors)) {
            // Store errors in transient — display on redirect
            set_transient('ss_errors_' . get_current_user_id(), $errors, 45);
            wp_safe_redirect(add_query_arg(
                ['page' => self::MENU_SLUG, 'ss_error' => '1'],
                admin_url('options-general.php')
            ));
            exit;
        }

        // Save clean data
        update_option(self::OPTION_KEY, $settings);

        // Post/Redirect/Get — prevents double-submit on refresh
        wp_safe_redirect(add_query_arg(
            ['page' => self::MENU_SLUG, 'ss_saved' => '1'],
            admin_url('options-general.php')
        ));
        exit;
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    /**
     * Sanitize raw POST data — each field sanitized by its type.
     */
    private function sanitize_settings(array $raw): array
    {
        return [
            'site_label'   => sanitize_text_field($raw['site_label']   ?? ''),
            'admin_email'  => sanitize_email($raw['admin_email']  ?? ''),
            'max_members'  => absint($raw['max_members']  ?? 0),
            'allow_signup' => (bool) ($raw['allow_signup'] ?? false),
            'welcome_msg'  => sanitize_textarea_field($raw['welcome_msg'] ?? ''),
        ];
    }

    /**
     * Validate sanitized data — business rules, not security.
     * Returns array of error messages (empty = valid).
     */
    private function validate_settings(array $settings): array
    {
        $errors = [];

        if (empty($settings['site_label'])) {
            $errors[] = 'Site label is required.';
        }

        if (! is_email($settings['admin_email'])) {
            $errors[] = 'Admin email must be a valid email address.';
        }

        if ($settings['max_members'] < 1 || $settings['max_members'] > 10000) {
            $errors[] = 'Max members must be between 1 and 10,000.';
        }

        return $errors;
    }

    /**
     * Get saved settings with safe defaults.
     */
    private function get_settings(): array
    {
        $saved = get_option(self::OPTION_KEY, []);

        // Merge with defaults — safe even if option doesn't exist yet
        return wp_parse_args($saved, [
            'site_label'   => '',
            'admin_email'  => get_option('admin_email'),
            'max_members'  => 100,
            'allow_signup' => true,
            'welcome_msg'  => '',
        ]);
    }
}
