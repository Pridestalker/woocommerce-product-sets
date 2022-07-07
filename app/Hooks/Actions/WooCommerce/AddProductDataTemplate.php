<?php

namespace WooProductSets\Hooks\Actions\WooCommerce;

use WooProductSets\Hooks\Hook;
use WooProductSets\System\View;

use function WooProductSets\app;

class AddProductDataTemplate extends Hook
{
    public static function hook_name()
    {
        return 'woocommerce_product_options_general_product_data';
    }

    public function hook()
    {
        $product = wc_get_product();
        
        app(View::class)->write('admin-template', ['product' => $product]);
    }

}
