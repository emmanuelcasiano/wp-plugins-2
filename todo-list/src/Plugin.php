<?php

namespace TodoList;

use TodoList\Database\Migrator;
use TodoList\Services\TodoService;

final class Plugin
{

    private static ?Plugin $instance = null;
    private function __construct() {}
    private function __clone() {}

    public static function get_instance(): static
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function activate(): void
    {
        $this->define_constants();
        (new Migrator())->run();
    }

    public function boot(): void
    {
        $this->define_constants();
        // Run migrations on every boot — safe because dbDelta is idempotent
        // In production you'd gate this behind a version check (see theory tab)
        (new Migrator())->maybe_upgrade();
        (new TodoService())->register();
    }

    private function define_constants(): void
    {
        define('TDL_VERSION', '1.0.0');
        define('TDL_DB_VERSION', '1.0');
        define('TDL_PATH', plugin_dir_path(dirname(__FILE__)));
    }
}
