<?php

namespace WooProductSets\Hooks\Actions\System;

use WooProductSets\Hooks\Hook;

class EnqueueScripts extends Hook
{
    public static function hook_name()
    {
        return 'admin_footer';
    }

    public function hook()
    {
        if (is_product()) {
            wp_enqueue_script('woocommerce-product-sets-main',);
        }
    }
}
