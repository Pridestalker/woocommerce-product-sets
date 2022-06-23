<?php

namespace WooProductSets\Hooks\Cron;

use WooProductSets\Hooks\Cron;

class StockCron extends Cron
{
    public static function hook_name()
    {
        return 'woo_product_set_cron';
    }

    public static function recurrence()
    {
        return 'every_five_minutes';
    }
}
