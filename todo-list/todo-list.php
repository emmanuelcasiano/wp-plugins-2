<?php

/**
 * Plugin Name: Todo List
 * Description: Ch.05 side project — custom table, full CRUD, migrations.
 * Version:     1.0.0
 * Requires PHP: 8.1
 * Text Domain: todo-list
 */

if (! defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

register_activation_hook(__FILE__, function () {
    TodoList\Plugin::get_instance()->activate();
});

add_action('plugins_loaded', function () {
    TodoList\Plugin::get_instance()->boot();
});
