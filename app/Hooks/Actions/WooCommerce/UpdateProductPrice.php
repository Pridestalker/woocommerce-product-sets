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
        if (!$product || $product->get_type() !== 'set') {
            return;
        }

        if (!isset($_POST[WC_Product_Set::K_PRICE_TYPE])) {
            return;
        }

        $this->updatePriceType($product);
        $this->updatePercentagePrice($product);
        $product->update_price();
    }

    protected function updatePriceType($product)
    {
        $product->update_meta_data(WC_Product_Set::K_PRICE_TYPE, $_POST[WC_Product_Set::K_PRICE_TYPE]);
    }

    protected function updatePercentagePrice($product)
    {
        if (!isset($_POST[WC_Product_Set::K_SET_PRICE_PERCENTAGE_FEE])) {
            return;
        }

        $product->update_meta_data(
            WC_Product_Set::K_SET_PRICE_PERCENTAGE_FEE,
            sanitize_text_field($_POST[WC_Product_Set::K_SET_PRICE_PERCENTAGE_FEE])
        );
    }

}
