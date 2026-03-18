<?php
// Available: $todos (array), $counts (array), $message (string)
if (! defined('ABSPATH')) exit;
$msgs = [
    'created' => 'Todo created.',
    'updated' => 'Todo updated.',
    'deleted' => 'Todo deleted.',
    'error'   => 'Something went wrong.',
];
?>
<div class="wrap">
    <h1><?php esc_html_e('My Todo List', 'todo-list'); ?></h1>

    <?php if ($message && isset($msgs[$message])) : ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html($msgs[$message]); ?></p>
        </div>
    <?php endif; ?>

    <!-- Stats bar -->
    <p>
        <?php
        printf(
            esc_html__('%d pending · %d in progress · %d done', 'todo-list'),
            (int) $counts['pending'],
            (int) $counts['in_progress'],
            (int) $counts['done']
        );
        ?>
    </p>

    <!-- Create form -->
    <h2><?php esc_html_e('Add todo', 'todo-list'); ?></h2>
    <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <?php wp_nonce_field('tdl_create', 'tdl_nonce'); ?>
        <input type="hidden" name="action" value="tdl_create">
        <input type="text" name="title" placeholder="Todo title..." class="regular-text" required>
        <select name="priority">
            <option value="0"><?php esc_html_e('Normal', 'todo-list'); ?></option>
            <option value="1"><?php esc_html_e('High',   'todo-list'); ?></option>
        </select>
        <input type="date" name="due_date">
        <?php submit_button(__('Add', 'todo-list'), 'primary', 'submit', false); ?>
    </form>

    <!-- Todo list -->
    <h2><?php esc_html_e('Todos', 'todo-list'); ?></h2>
    <?php if (empty($todos)) : ?>
        <p><?php esc_html_e('No todos yet.', 'todo-list'); ?></p>
    <?php else : ?>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Title',    'todo-list'); ?></th>
                    <th><?php esc_html_e('Status',   'todo-list'); ?></th>
                    <th><?php esc_html_e('Priority', 'todo-list'); ?></th>
                    <th><?php esc_html_e('Due',      'todo-list'); ?></th>
                    <th><?php esc_html_e('Actions',  'todo-list'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($todos as $todo) : ?>
                    <tr>
                        <td><?php echo esc_html($todo->title); ?></td>
                        <td><?php echo esc_html($todo->status); ?></td>
                        <td><?php echo $todo->priority ? '<strong>High</strong>' : 'Normal'; ?></td>
                        <td><?php echo $todo->due_date ? esc_html($todo->due_date) : '—'; ?></td>
                        <td>
                            <!-- Mark done -->
                            <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline">
                                <?php wp_nonce_field('tdl_update', 'tdl_nonce'); ?>
                                <input type="hidden" name="action" value="tdl_update">
                                <input type="hidden" name="id" value="<?php echo absint($todo->id); ?>">
                                <input type="hidden" name="status" value="done">
                                <input type="hidden" name="title" value="<?php echo esc_attr($todo->title); ?>">
                                <button type="submit" class="button button-small">✓ Done</button>
                            </form>
                            <!-- Delete -->
                            <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline">
                                <?php wp_nonce_field('tdl_delete', 'tdl_nonce'); ?>
                                <input type="hidden" name="action" value="tdl_delete">
                                <input type="hidden" name="id" value="<?php echo absint($todo->id); ?>">
                                <button type="submit" class="button button-small button-link-delete">✕</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>