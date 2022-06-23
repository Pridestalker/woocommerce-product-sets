<?php

namespace WooProductSets\Hooks\Actions\System;

use WooProductSets\Hooks\Cron;
use WooProductSets\Hooks\Hook;

/**
 * The action responsible for the de-installation
 * of the plugin. It handles the removal of cron jobs.
 *
 * @version 1.0.0
 * @author Mitch Hijlkema <mitch@mitchhijlkema.nl>
 * @copyright 2021 Mitch Hijlkema
 */
class DeInstall extends Hook
{
    public function hook()
    {
        $this->remove_cron_job();
    }

    private function remove_cron_job()
    {
        foreach (\WooProductSets\config('hooks.cron_jobs') as $cron) {
            /**
             * @type Cron $cron
             */
            $timestamp = wp_next_scheduled($cron::hook_name());
            wp_unschedule_event($timestamp, $cron::hook_name());
        }
    }

    public static function hook_name()
    {
        return 'woo_product_sets_deactivation';
    }
}
