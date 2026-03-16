<?php

namespace OopSkeleton\Services;

use OopSkeleton\Helpers\Logger;

/**
 * ExampleService — demonstrates the service class pattern.
 *
 * Rules every service class follows:
 *   1. Constructor accepts dependencies (injected, not created internally).
 *   2. register() is the only method called from outside — it wires hooks.
 *   3. All hook callbacks are public methods (WordPress requires it).
 *   4. Business logic methods are private.
 */
class ExampleService
{

    // Dependency injection — Logger is passed in, not created here
    public function __construct(
        private readonly Logger $logger
    ) {}

    /**
     * Wire this service into WordPress.
     * Called once by Plugin::register_services().
     */
    public function register(): void
    {
        add_action('init',        [$this, 'on_init']);
        add_filter('the_title',   [$this, 'filter_title'], 10, 2);
        add_action('admin_init',  [$this, 'on_admin_init']);
    }

    // ─── Hook callbacks (public — WordPress needs to call them) ─────────────

    public function on_init(): void
    {
        $this->logger->debug('ExampleService::on_init fired');
    }

    public function filter_title(string $title, int $post_id): string
    {
        // Always return in filters — even unmodified value
        return $title;
    }

    public function on_admin_init(): void
    {
        $this->logger->debug('ExampleService::on_admin_init fired');
    }

    // ─── Private business logic (not exposed as hooks) ───────────────────────

    private function do_something_internal(): void
    {
        // Only callable from within this class
    }
}
