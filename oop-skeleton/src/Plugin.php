<?php

namespace OopSkeleton;

use OopSkeleton\Services\ExampleService;
use OopSkeleton\Helpers\Logger;
use OopSkeleton\Services\PostMetaService;

/**
 * Main plugin class.
 * Responsible for: booting, defining constants, wiring services.
 * Nothing else.
 */
final class Plugin
{

    private static ?Plugin $instance = null;

    /**
     * Private constructor enforces singleton.
     * Declare dependencies here as constructor params when using DI.
     */
    private function __construct() {}

    /** No cloning allowed */
    private function __clone() {}

    /**
     * Get or create the single plugin instance.
     */
    public static function get_instance(): static
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * Boot sequence — called once from the main plugin file.
     * Order matters: constants → services → hooks.
     */
    public function boot(): void
    {
        $this->define_constants();
        $this->register_services();

        // Plugin-level hooks (activation/deactivation live in the main file,
        // but lifecycle hooks that need class context go here)
        add_action('init', [$this, 'on_init']);
    }

    /**
     * Central constant definitions.
     * Prefix with your plugin slug — always.
     */
    private function define_constants(): void
    {
        define('OPSKEL_VERSION',  '1.0.0');
        define('OPSKEL_PATH',     plugin_dir_path(dirname(__FILE__)));
        define('OPSKEL_URL',      plugin_dir_url(dirname(__FILE__)));
        define('OPSKEL_BASENAME', plugin_basename(dirname(__FILE__) . '/oop-skeleton.php'));
    }

    /**
     * Instantiate and register all service classes.
     * Each service wires its own hooks via register().
     */
    private function register_services(): void
    {
        $logger = new Logger();

        (new ExampleService($logger))->register();
        // Future services go here:
        // ( new MemberService( $logger ) )->register();
        // ( new AdminController( $logger ) )->register();
        (new PostMetaService($logger))->register();
    }

    /**
     * Plugin-level init hook.
     */
    public function on_init(): void
    {
        // Load textdomain for translations
        load_plugin_textdomain(
            'oop-skeleton',
            false,
            dirname(OPSKEL_BASENAME) . '/languages'
        );
    }
}
