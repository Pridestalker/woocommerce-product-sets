<?php

namespace WooProductSets;

use WooProductSets\Hooks\Hook;
use WooProductSets\System\Container;

class Plugin
{

    protected static ?Plugin $_instance = null;

    private function __construct()
    {
        if (!empty(config('hooks.post_types'))) {
            $this->register_post_types();
        }
        $this->register_taxonomies();
        $this->register_hooks();
        add_action('admin_footer', [$this, 'registerScripts']);
    }

    protected function register_post_types()
    {
        $post_types = config('hooks.post_types');

        $this->registerActionsFromArray($post_types);
    }

    protected function registerActionsFromArray($hook_arr = [])
    {
        foreach ($hook_arr as $hook) {
            /**
             * @type Hook $class
             */
            $class = Container::make($hook);

            if (is_array($class::hook_name())) {
                foreach ($class::hook_name() as $hook_name) {
                    add_action($hook_name, [$class, 'hook'], $class::priority(), $class::parameter_count());
                }
            } else {
                add_action($class::hook_name(), [$class, 'hook'], $class::priority(), $class::parameter_count());
            }
        }
    }

    protected function register_taxonomies()
    {
        $taxonomies = config('hooks.taxonomies');

        $this->registerActionsFromArray($taxonomies);
    }

    protected function register_hooks()
    {
        $actions = config('hooks.actions');
        $filters = config('hooks.filters');
        $this->registerActionsFromArray($actions);
        $this->registerFiltersFromArray($filters);
    }

    protected function registerFiltersFromArray($hook_arr = [])
    {
        foreach ($hook_arr as $hook) {
            /**
             * @type Hook $class
             */
            $class = Container::make($hook);

            if (is_array($class::hook_name())) {
                foreach ($class::hook_name() as $hook_name) {
                    add_filter($class::hook_name(), [
                        $class,
                        'hook'
                    ], $class::priority(), $class::parameter_count());
                }
            } else {
                add_filter($class::hook_name(), [$class, 'hook'], $class::priority(), $class::parameter_count());
            }
        }
    }

    public function registerScripts()
    {
        wp_register_script(
            'woocommerce-product-sets-main',
            plugin_dir_url(WOO_PROD_SETS_FILE) . '/dist/scripts/app.js',
            ['jquery'],
            filemtime(WOO_PROD_SETS_DIR . '/dist/scripts/app.js'),
            true
        );
    }

    /**
     * Gets the base plugin file to hook in to everything.
     *
     * @return Plugin
     */
    public static function get_instance(): Plugin
    {
        if (static::$_instance === null) {
            static::$_instance = new self();
        }

        return self::$_instance;
    }
}
