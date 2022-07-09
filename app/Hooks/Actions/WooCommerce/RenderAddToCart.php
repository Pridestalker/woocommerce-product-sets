<?php

namespace WooProductSets\Hooks\Actions\WooCommerce;

use WooProductSets\Hooks\Hook;
use WooProductSets\System\View;

use function WooProductSets\app;

class RenderAddToCart extends Hook
{
    public function hook()
    {
        if (get_theme_file_path('woocommerce/single-product/add-to-cart/set.php')) {
            require_once get_theme_file_path('woocommerce/single-product/add-to-cart/set.php');
            return;
        }

        app(View::class)
            ->write('add-to-cart', [
                'product' => wc_get_product(),
            ]);
    }

    public static function hook_name()
    {
        return 'woocommerce_set_add_to_cart';
    }
}