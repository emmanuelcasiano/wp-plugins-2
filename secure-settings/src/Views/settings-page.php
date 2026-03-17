<?php
// Available: $settings (array), $saved (bool)
// This file only outputs HTML. No logic, no DB calls.
if (! defined('ABSPATH')) exit;

$errors = get_transient('ss_errors_' . get_current_user_id());
if ($errors) {
    delete_transient('ss_errors_' . get_current_user_id());
}
?>
<div class="wrap">
    <h1><?php esc_html_e('Secure Settings', 'secure-settings'); ?></h1>

    <?php if ($saved) : ?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e('Settings saved.', 'secure-settings'); ?></p>
        </div>
    <?php endif; ?>

    <?php if (! empty($errors)) : ?>
        <div class="notice notice-error">
            <?php foreach ($errors as $error) : ?>
                <p><?php echo esc_html($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <?php wp_nonce_field('ss_save_settings', 'ss_nonce'); ?>
        <input type="hidden" name="action" value="ss_save">

        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">
                    <label for="site_label">
                        <?php esc_html_e('Site label', 'secure-settings'); ?>
                    </label>
                </th>
                <td>
                    <input type="text"
                        name="site_label"
                        id="site_label"
                        class="regular-text"
                        value="<?php echo esc_attr($settings['site_label']); ?>">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="admin_email">
                        <?php esc_html_e('Admin email', 'secure-settings'); ?>
                    </label>
                </th>
                <td>
                    <input type="email"
                        name="admin_email"
                        id="admin_email"
                        class="regular-text"
                        value="<?php echo esc_attr($settings['admin_email']); ?>">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="max_members">
                        <?php esc_html_e('Max members', 'secure-settings'); ?>
                    </label>
                </th>
                <td>
                    <input type="number"
                        name="max_members"
                        id="max_members"
                        class="small-text"
                        min="1" max="10000"
                        value="<?php echo esc_attr($settings['max_members']); ?>">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <?php esc_html_e('Allow signup', 'secure-settings'); ?>
                </th>
                <td>
                    <label>
                        <input type="checkbox"
                            name="allow_signup"
                            value="1"
                            <?php checked($settings['allow_signup']); ?>>
                        <?php esc_html_e('Allow new member registrations', 'secure-settings'); ?>
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="welcome_msg">
                        <?php esc_html_e('Welcome message', 'secure-settings'); ?>
                    </label>
                </th>
                <td>
                    <textarea name="welcome_msg"
                        id="welcome_msg"
                        class="large-text"
                        rows="4"><?php echo esc_textarea($settings['welcome_msg']); ?></textarea>
                </td>
            </tr>
        </table>

        <?php submit_button(__('Save settings', 'secure-settings')); ?>
    </form>
</div>