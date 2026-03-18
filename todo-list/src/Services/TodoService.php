<?php

namespace TodoList\Services;

use TodoList\Models\TodoModel;

/**
 * TodoService — handles HTTP layer (hooks, requests, output).
 * Delegates all data access to TodoModel.
 */
class TodoService
{

    private TodoModel $model;

    public function __construct()
    {
        $this->model = new TodoModel();
    }

    public function register(): void
    {
        add_action('admin_menu',              [$this, 'register_menu']);
        add_action('admin_post_tdl_create',   [$this, 'handle_create']);
        add_action('admin_post_tdl_update',   [$this, 'handle_update']);
        add_action('admin_post_tdl_delete',   [$this, 'handle_delete']);
    }

    public function register_menu(): void
    {
        add_menu_page(
            'Todo List',
            'Todo List',
            'read',                     // any logged-in user
            'todo-list',
            [$this, 'render_page'],
            'dashicons-checkbox',
            30
        );
    }

    // ─── Render ───────────────────────────────────────────────────────────────

    public function render_page(): void
    {
        $user_id = get_current_user_id();
        $todos   = $this->model->find_by_user($user_id, [
            'status'   => sanitize_key($_GET['filter'] ?? ''),
            'order_by' => 'created_at',
            'order'    => 'DESC',
        ]);
        $counts  = $this->model->count_by_user($user_id);
        $message = sanitize_text_field($_GET['tdl_msg'] ?? '');

        require TDL_PATH . 'src/Views/admin-page.php';
    }

    // ─── Handlers ─────────────────────────────────────────────────────────────

    public function handle_create(): void
    {
        $this->verify_nonce('tdl_create');
        $this->require_login();

        $id = $this->model->create([
            'user_id'  => get_current_user_id(),
            'title'    => sanitize_text_field($_POST['title']    ?? ''),
            'priority' => absint($_POST['priority'] ?? 0),
            'due_date' => sanitize_text_field($_POST['due_date'] ?? '') ?: null,
        ]);

        $this->redirect($id ? 'created' : 'error');
    }

    public function handle_update(): void
    {
        $this->verify_nonce('tdl_update');
        $this->require_login();

        $id      = absint($_POST['id'] ?? 0);
        $user_id = get_current_user_id();

        // Ownership check — users can only update their own todos
        if (! $this->model->belongs_to($id, $user_id)) {
            wp_die(esc_html__('You do not own this item.', 'todo-list'), 403);
        }

        $this->model->update($id, [
            'status'   => sanitize_key($_POST['status']   ?? ''),
            'title'    => sanitize_text_field($_POST['title']    ?? ''),
            'priority' => absint($_POST['priority'] ?? 0),
            'due_date' => sanitize_text_field($_POST['due_date'] ?? '') ?: null,
        ]);

        $this->redirect('updated');
    }

    public function handle_delete(): void
    {
        $this->verify_nonce('tdl_delete');
        $this->require_login();

        $id      = absint($_POST['id'] ?? 0);
        $user_id = get_current_user_id();

        if (! $this->model->belongs_to($id, $user_id)) {
            wp_die(esc_html__('You do not own this item.', 'todo-list'), 403);
        }

        $this->model->delete($id);
        $this->redirect('deleted');
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    private function verify_nonce(string $action): void
    {
        if (! wp_verify_nonce($_POST['tdl_nonce'] ?? '', $action)) {
            wp_die(esc_html__('Security check failed.', 'todo-list'), 403);
        }
    }

    private function require_login(): void
    {
        if (! is_user_logged_in()) {
            wp_die(esc_html__('You must be logged in.', 'todo-list'), 403);
        }
    }

    private function redirect(string $msg): void
    {
        wp_safe_redirect(add_query_arg(
            ['page' => 'todo-list', 'tdl_msg' => $msg],
            admin_url('admin.php')
        ));
        exit;
    }
}
