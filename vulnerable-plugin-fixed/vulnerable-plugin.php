<?php

/**
 * Plugin Name: Vulnerable Plugin (FIXED)
 * Description: Ch.04 — all four vulnerabilities corrected.
 * Version:     2.0.0
 */

if (! defined('ABSPATH')) {
    exit;
}

add_action('admin_menu', 'vp_add_page');

function vp_add_page(): void
{
    add_menu_page(
        'Vulnerable',
        'Vulnerable',
        'manage_options', // FIX 1: admins only — was 'read'
        'vulnerable-plugin',
        'vp_render_page'
    );
}

function vp_render_page(): void
{
    $message = get_option('vp_message', '');
?>
    <div class="wrap">
        <h1><?php esc_html_e('Vulnerable Plugin (Fixed)', 'vp'); ?></h1>

        <?php if (isset($_GET['saved'])) : ?>
            <div class="notice notice-success">
                <!-- FIX 2: esc_html() prevents XSS — was raw echo -->
                <p>Saved! Your name: <?php echo esc_html($_GET['name'] ?? ''); ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <!-- FIX 3: nonce field added — prevents CSRF -->
            <?php wp_nonce_field('vp_save_message', 'vp_nonce'); ?>
            <input type="hidden" name="action" value="vp_save">

            <textarea name="vp_message" rows="5" style="width:100%"><?php
                                                                    echo esc_textarea($message); // FIX 2: escaped for textarea context
                                                                    ?></textarea>
            <br><br>
            <input type="submit" value="Save" class="button button-primary">
        </form>

        <h2><?php esc_html_e('Search users', 'vp'); ?></h2>
        <form method="GET">
            <input type="hidden" name="page" value="vulnerable-plugin">
            <input type="text" name="search"
                value="<?php echo esc_attr($_GET['search'] ?? ''); ?>"> <!-- FIX 2 -->
            <input type="submit" value="Search">
        </form>

        <?php
        if (! empty($_GET['search'])) {
            global $wpdb;

            // FIX 4: prepared statement — was raw string concatenation
            $search  = '%' . $wpdb->esc_like(sanitize_text_field($_GET['search'])) . '%';
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT user_login, user_email FROM {$wpdb->users}
                     WHERE user_login LIKE %s",
                    $search
                )
            );

            foreach ($results as $user) {
                // FIX 2: esc_html on all output — was raw echo
                printf(
                    '<p>%s — %s</p>',
                    esc_html($user->user_login),
                    esc_html($user->user_email)
                );
            }
        }
        ?>
    </div>
<?php
}

add_action('admin_post_vp_save', 'vp_save');

function vp_save(): void
{
    // FIX 3: verify nonce first
    if (! wp_verify_nonce($_POST['vp_nonce'] ?? '', 'vp_save_message')) {
        wp_die('Security check failed.', 403);
    }

    // FIX 1: verify capability
    if (! current_user_can('manage_options')) {
        wp_die('Insufficient permissions.', 403);
    }

    // FIX 2+4: sanitize before saving
    $message = sanitize_textarea_field($_POST['vp_message'] ?? '');
    update_option('vp_message', $message);

    // FIX 2: never redirect with raw POST data in URL
    wp_safe_redirect(admin_url('admin.php?page=vulnerable-plugin&saved=1'));
    exit;
}
