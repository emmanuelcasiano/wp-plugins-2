<?php

namespace TodoList\Database;

/**
 * Migrator — owns all schema creation and upgrades.
 *
 * Key concepts demonstrated:
 *   - dbDelta() for safe idempotent table creation
 *   - version comparison for selective migration runs
 *   - $wpdb->prefix for multisite-safe table names
 */
class Migrator
{

    // Bump this string every time the schema changes
    private const DB_VERSION = '1.1';
    private const OPTION_KEY = 'tdl_db_version';

    /**
     * Called on activation — always run all migrations fresh.
     */
    public function run(): void
    {
        $this->migrate_to_1_0();
        update_option(self::OPTION_KEY, self::DB_VERSION);
    }

    /**
     * Called on every boot — only runs if schema is behind.
     */
    public function maybe_upgrade(): void
    {
        $installed = get_option(self::OPTION_KEY, '0');

        if (version_compare($installed, self::DB_VERSION, '>=')) {
            return; // Already up to date — skip
        }

        // Run only what's needed
        if (version_compare($installed, '1.0', '<')) {
            $this->migrate_to_1_0();
        }

        // Future: if ( version_compare( $installed, '1.1', '<' ) ) { ... }
        if (version_compare($installed, '1.1', '<')) {
            $this->migrate_to_1_1(); // new
        }

        update_option(self::OPTION_KEY, self::DB_VERSION);
    }

    // ─── Migrations ───────────────────────────────────────────────────────────

    private function migrate_to_1_0(): void
    {
        global $wpdb;

        // Always require this before dbDelta()
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset = $wpdb->get_charset_collate();
        $table   = $wpdb->prefix . 'tdl_todos';

        // dbDelta() formatting rules are strict — follow exactly:
        // - two spaces before PRIMARY KEY
        // - lowercase column types
        // - each column on its own line
        $sql = "CREATE TABLE {$table} (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id bigint(20) UNSIGNED NOT NULL,
        title varchar(255) NOT NULL,
        status varchar(20) NOT NULL DEFAULT 'pending',
        priority tinyint(1) NOT NULL DEFAULT 0,
        due_date date DEFAULT NULL,
        created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY user_id (user_id),
        KEY status (status),
        KEY due_date (due_date)
        ) {$charset};";

        dbDelta($sql);
    }

    /**
     * Example of a v1.1 migration — adds a notes column.
     * Uncomment and bump DB_VERSION to '1.1' to test schema upgrading.
     */
    private function migrate_to_1_1(): void
    {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $table = $wpdb->prefix . 'tdl_todos';
        $sql   = "CREATE TABLE {$table} (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id bigint(20) UNSIGNED NOT NULL,
        title varchar(255) NOT NULL,
        notes text DEFAULT NULL,
        status varchar(20) NOT NULL DEFAULT 'pending',
        priority tinyint(1) NOT NULL DEFAULT 0,
        due_date date DEFAULT NULL,
        created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY user_id (user_id),
        KEY status (status),
        KEY due_date (due_date)
    ) {$wpdb->get_charset_collate()};";
        dbDelta($sql);
    }

    /**
     * Called from uninstall.php — drops all our tables.
     */
    public function drop_all(): void
    {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}tdl_todos");
        delete_option(self::OPTION_KEY);
    }
}
