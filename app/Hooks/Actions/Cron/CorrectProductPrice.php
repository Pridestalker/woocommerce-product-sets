<?php

namespace WooProductSets\Hooks\Actions\Cron;

use WooProductSets\Hooks\Hook;
use WooProductSets\Models\WC_Product_Set;

class CorrectProductPrice extends Hook
{
    public static function hook_name()
    {
        return [
            'woo_product_set_cron',
            'woo_product_set_stock_cron',
        ];
    }

    public function hook()
    {
        $products = $this->get_products();

        foreach ($products as $product) {
            $this->handle_product($product);
        }
    }

    /**
     * @return WC_Product_Set[]
     */
    private function get_products(): array
    {
        return wc_get_products([
            'limit' => -1,
            'type' => 'set',
        ]);
    }

    private function handle_product(WC_Product_Set $product)
    {
        $product->update_price();
    }
}
