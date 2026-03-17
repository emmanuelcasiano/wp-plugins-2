<?php

namespace SecureSettings;

use SecureSettings\Services\SettingsService;

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

    public function boot(): void
    {
        define('SS_PATH', plugin_dir_path(dirname(__FILE__)));
        define('SS_VERSION', '1.0.0');
        (new SettingsService())->register();
    }
}
