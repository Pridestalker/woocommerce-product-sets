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
        if (!is_admin()) {
            return;
        }

        if (!isset($_GET['post'])) {
            return;
        }

        if (get_post_type($_GET['post']) === 'product') {
            wp_enqueue_script('woocommerce-product-sets-main');
        }
    }
}
