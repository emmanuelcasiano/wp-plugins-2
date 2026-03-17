<?php

/**
 * Plugin Name: Vulnerable Plugin (Exercise)
 * Description: Ch.04 side project — intentionally insecure. Find and fix the bugs.
 * Version:     1.0.0
 */

if (! defined('ABSPATH')) {
    exit;
}

// Adds an admin page
add_action('admin_menu', 'vp_add_page');

function vp_add_page(): void
{
    add_menu_page('Vulnerable', 'Vulnerable', 'read', 'vulnerable-plugin', 'vp_render_page');
    //                                          ^^^^
    // BUG 1: 'read' means ANY logged-in user can access this admin page
}

function vp_render_page(): void
{
    $message = get_option('vp_message', '');
?>
    <div class="wrap">
        <h1>Vulnerable Plugin</h1>

        <?php if (isset($_GET['saved'])) : ?>
            <div class="notice notice-success">
                <!-- BUG 2: unescaped output — XSS via URL parameter -->
                <p>Saved! Your name: <?php echo $_GET['name']; ?></p>
            </div>
        <?php endif; ?>

        <form method="POST">
            <!-- BUG 3: no nonce field — CSRF attack possible -->
            <textarea name="vp_message" rows="5" style="width:100%"><?php
                                                                    // BUG 2 also here — unescaped DB value in attribute context
                                                                    echo $message;
                                                                    ?></textarea>
            <br><br>
            <input type="submit" value="Save" class="button button-primary">
        </form>

        <h2>Search users</h2>
        <form method="GET">
            <input type="text" name="search" value="">
            <input type="submit" value="Search">
        </form>

        <?php
        if (isset($_GET['search'])) {
            global $wpdb;
            // BUG 4: SQL injection — raw $_GET in query
            $results = $wpdb->get_results(
                "SELECT user_login, user_email FROM {$wpdb->users}
                 WHERE user_login LIKE '%" . $_GET['search'] . "%'"
            );
            foreach ($results as $user) {
                // BUG 2 again — unescaped DB values in output
                echo '<p>' . $user->user_login . ' — ' . $user->user_email . '</p>';
            }
        }
        ?>
    </div>
<?php
}

add_action('admin_post_vp_save', 'vp_save');

function vp_save(): void
{
    // BUG 3 (continued): no nonce check, no capability check, no sanitization
    $message = $_POST['vp_message'];
    update_option('vp_message', $message);
    wp_redirect(admin_url('admin.php?page=vulnerable-plugin&saved=1&name=' . $_POST['vp_message']));
    exit;
}
