<?php

namespace WooProductSets\Hooks\Actions\WooCommerce;

use WooProductSets\Hooks\Hook;
use WooProductSets\Models\WC_Product_Set;

class UpdateProductPrice extends Hook
{
    public static function hook_name()
    {
        return 'post_updated';
    }

    public function hook($post_id)
    {
        /** @var WC_Product_Set $product */
        $product = wc_get_product($post_id);
        if (!$product || $product->get_type !== 'set') {
            return;
        }

        if (!isset($_POST['_price_type'])) {
            return;
        }

        $product->update_meta_data('_price_type', $_POST['_price_type']);
        $product->update_price();
    }
}
