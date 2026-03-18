<?php

namespace TodoList\Models;

/**
 * TodoModel — all database interaction for the todos table.
 *
 * Rules for Model classes:
 *   - Only this class knows the table name
 *   - All queries go through $wpdb->prepare()
 *   - Returns typed objects or null — never raw $wpdb output
 *   - No WordPress hooks, no HTTP logic, no output
 */
class TodoModel
{

    private string $table;

    public function __construct()
    {
        global $wpdb;
        $this->table = $wpdb->prefix . 'tdl_todos';
    }

    // ─── Create ───────────────────────────────────────────────────────────────

    public function create(array $data): int|false
    {
        global $wpdb;

        $result = $wpdb->insert(
            $this->table,
            [
                'user_id'  => $data['user_id'],
                'title'    => $data['title'],
                'status'   => $data['status']   ?? 'pending',
                'priority' => $data['priority'] ?? 0,
                'due_date' => $data['due_date']  ?? null,
            ],
            ['%d', '%s', '%s', '%d', '%s']
        );

        return $result !== false ? $wpdb->insert_id : false;
    }

    // ─── Read ─────────────────────────────────────────────────────────────────

    public function find(int $id): ?object
    {
        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table} WHERE id = %d",
                $id
            )
        );
    }

    public function find_by_user(int $user_id, array $filters = []): array
    {
        global $wpdb;

        // Base query — user_id is always required
        $where  = 'WHERE user_id = %d';
        $values = [$user_id];

        // Optional status filter
        if (! empty($filters['status'])) {
            $where   .= ' AND status = %s';
            $values[] = sanitize_key($filters['status']);
        }

        // Optional ordering — whitelist to prevent injection
        $allowed_order = ['created_at', 'due_date', 'priority', 'title'];
        $order_by      = in_array($filters['order_by'] ?? '', $allowed_order, true)
            ? $filters['order_by']
            : 'created_at';
        $order_dir = 'ASC' === strtoupper($filters['order'] ?? '') ? 'ASC' : 'DESC';

        $sql = $wpdb->prepare(
            "SELECT * FROM {$this->table} {$where} ORDER BY {$order_by} {$order_dir}",
            ...$values
        );

        return $wpdb->get_results($sql) ?: [];
    }

    public function count_by_user(int $user_id): array
    {
        global $wpdb;

        // Group by status — returns counts per status in one query
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT status, COUNT(*) as count
                 FROM {$this->table}
                 WHERE user_id = %d
                 GROUP BY status",
                $user_id
            )
        );

        // Normalize to a predictable array shape
        $counts = ['pending' => 0, 'in_progress' => 0, 'done' => 0];
        foreach ($rows as $row) {
            if (isset($counts[$row->status])) {
                $counts[$row->status] = (int) $row->count;
            }
        }

        return $counts;
    }

    // ─── Update ───────────────────────────────────────────────────────────────

    public function update(int $id, array $data): bool
    {
        global $wpdb;

        // Only update fields that were provided
        $fields  = [];
        $formats = [];
        $values  = [];

        if (isset($data['title'])) {
            $fields[]  = 'title = %s';
            $values[]  = $data['title'];
        }
        if (isset($data['status'])) {
            $fields[]  = 'status = %s';
            $values[]  = $data['status'];
        }
        if (isset($data['priority'])) {
            $fields[]  = 'priority = %d';
            $values[]  = (int) $data['priority'];
        }
        if (array_key_exists('due_date', $data)) {
            $fields[]  = 'due_date = %s';
            $values[]  = $data['due_date']; // can be null
        }

        if (empty($fields)) {
            return false; // Nothing to update
        }

        $values[] = $id; // for WHERE clause

        $sql = $wpdb->prepare(
            "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = %d",
            ...$values
        );

        return $wpdb->query($sql) !== false;
    }

    // ─── Delete ───────────────────────────────────────────────────────────────

    public function delete(int $id): bool
    {
        global $wpdb;

        return (bool) $wpdb->delete(
            $this->table,
            ['id' => $id],
            ['%d']
        );
    }

    /**
     * Ownership check — always verify before update/delete.
     * Returns true only if the given user_id owns this todo.
     */
    public function belongs_to(int $id, int $user_id): bool
    {
        global $wpdb;

        $owner = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT user_id FROM {$this->table} WHERE id = %d",
                $id
            )
        );

        return (int) $owner === $user_id;
    }
}
