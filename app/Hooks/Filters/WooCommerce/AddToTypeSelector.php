<?php

namespace WooProductSets\Hooks\Filters\WooCommerce;

use WooProductSets\Hooks\Hook;

class AddToTypeSelector extends Hook
{
    public static function hook_name()
    {
        return 'product_type_selector';
    }

    public function hook(array $types)
    {
        if (!isset($types['set'])) {
            $types['set'] = __('Product Set', 'woocommerce-product-sets');
        }

        return $types;
    }
}
