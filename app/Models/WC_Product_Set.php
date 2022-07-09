<?php

namespace WooProductSets\Models;

/**
 * The WC_Product class extension WC_Product_Set. It represents a set of simple products.
 *
 * @author Mitch Hijlkema <mitch@mitchhijlkema.nl>
 * @version 2.0.0
 * @copyright Copyright (c) 2021
 */
class WC_Product_Set extends \WC_Product
{
    /**
     * Meta key for child products
     *
     * @var string K_CHILD_PRODUCT
     */
    public const K_CHILD_PRODUCT = '_child_product_ids';

    /**
     * Meta key for set price type
     *
     * @var string K_PRICE_TYPE
     */
    public const K_PRICE_TYPE = '_price_type';

    /**
     * Meta key for set price
     *
     * @var string K_SET_PRICE
     */
    public const K_SET_PRICE = '_set_price';

    /**
     * Meta key for the percentage fee set on the price.
     *
     * @var string K_SET_PRICE_PERCENTAGE_FEE
     */
    public const K_SET_PRICE_PERCENTAGE_FEE = '_set_price_percentage_fee';

    /**
     * Get the product type.
     *
     * @return string the product type
     */
    public function get_type(): string
    {
        return 'set';
    }

    /**
     * Regular product price. dynamically fetched.
     *
     * @param string $context
     *
     * @return string
     */
    public function get_regular_price($context = 'view'): string
    {
        $price = 0.00;
        $products = $this->get_child_products_as_wc_product();

        foreach ($products as $product) {
            $price += (float)$product->get_price('edit');
        }

        return $price;
    }

    /**
     * All child products as WC_Product.
     *
     * @return \WC_Product[]
     */
    public function get_child_products_as_wc_product(): array
    {
        return array_filter(
            array_map(static function ($id) {
                return \wc_get_product($id);
            }, $this->get_child_products())
        );
    }

    /**
     * Gets all child products.
     *
     * @return int[]
     */
    public function get_child_products(): array
    {
        do_action('WooProductSet/models/get-children/post', $this->get_id());
        $children = apply_filters(
            'WooProductSet/models/get-children',
            $this->get_meta(self::K_CHILD_PRODUCT)
        );

        return $children ?: [];
    }

    /**
     * Product price.
     *
     * @param string $context
     *
     * @return string
     */
    public function get_price($context = 'view')
    {
        return $this->get_meta(static::K_SET_PRICE, true, $context);
    }

    /**
     * @param string $context
     *
     * @return string
     */
    public function get_sale_price($context = 'view'): string
    {
        return wc_price($this->get_meta(static::K_SET_PRICE));
    }

    /**
     * Dynamically fetches the product stock status based on its children.
     *
     * @param string $context
     *
     * @return mixed|string|void
     */
    public function get_stock_status($context = 'view')
    {
        $children = $this->get_child_products_as_wc_product();
        $stock_status = 'instock';

        foreach ($children as $child) {
            $stock_status_child = $child->get_stock_status('edit');

            if ($stock_status_child === 'outofstock') {
                $stock_status = $stock_status_child;
                break;
            }

            if ($stock_status_child === 'onbackorder') {
                $stock_status = $stock_status_child;
                break;
            }
        }

        return apply_filters('Elderbraum/WooProductSet/models/stock-status', $stock_status);
    }

    /**
     * Calculate the stock for the product based on its children.
     *
     * @param string $context
     *
     * @return int
     */
    public function get_stock_quantity($context = 'view')
    {
        $baseStock = PHP_INT_MAX;

        foreach ($this->get_child_products_as_wc_product() as $childProduct) {
            if ($childProduct->get_stock_quantity('edit') < $baseStock) {
                $baseStock = $childProduct->get_stock_quantity('edit');
            }
        }

        return $baseStock;
    }

    /**
     * Product is default on sale if it uses a dynamic sum price.
     *
     * @param string $context
     *
     * @return bool
     */
    public function is_on_sale($context = 'view'): bool
    {
        return $this->get_meta(static::K_PRICE_TYPE) !== 'dynamic-sum';
    }

    /**
     * Updates the product price data to match the current set items.
     *
     * @since 2.0.0
     */
    public function update_price()
    {
        $newPrice = 0.00;

        $this->delete_meta_data('_sale_price');
        $this->delete_meta_data('_regular_price');
        $this->delete_meta_data('_price');

        foreach ($this->get_child_products() as $child_product) {
            $childProductTemp = wc_get_product($child_product);
            $newPrice += (float)$childProductTemp->get_price('edit');
        }

        $this->add_meta_data('_regular_price', $newPrice);

        switch ($this->get_meta(static::K_PRICE_TYPE)) {
            case 'fixed':
                if (!isset($_POST[static::K_SET_PRICE])) {
                    break;
                }
                $price = sanitize_text_field($_POST[static::K_SET_PRICE]);
                $this->update_meta_data('_sale_price', $price);
                $this->set_price($newPrice);
                $this->update_meta_data(static::K_SET_PRICE, $newPrice);
                break;
            case 'dynamic-sum':
                $this->update_meta_data('_price', $newPrice);
                $this->update_meta_data(static::K_SET_PRICE, $newPrice);
                break;
            case 'dynamic-percentage':
                $reducedPrice = $newPrice * (int)$this->get_meta(self::K_SET_PRICE_PERCENTAGE_FEE) / 100;
                $this->update_meta_data('_sale_price', $newPrice);
                $this->update_meta_data('_price', $reducedPrice);
                $this->update_meta_data(static::K_SET_PRICE, $reducedPrice);
                break;
        }

        $this->save_meta_data();;
        $this->save();
        do_action('woocommerce_updated_product_price', $this->get_id());
    }

    /**
     * Gets the current child products and checks their validity.
     *
     * @return void
     *
     * @since 2.0.0
     */
    public function sync_child_products()
    {
        $new_ids = [];
        $ids = $this->get_child_products();

        foreach ($ids as $id) {
            $product = wc_get_product($id);

            if (!$product) {
                continue;
            }

            if (!get_post_status($id) === 'publish') {
                continue;
            }

            $new_ids[] = $id;
        }

        $this->set_child_products($new_ids);
        $this->save_meta_data();
        $this->save();
    }

    /**
     * Sets the current child products.
     *
     * @param array $data Product ids
     */
    public function set_child_products(array $data = []): void
    {
        do_action('Elderbraum/WooProductSet/models/store-children/pre', $data, $this->get_id());
        $data = apply_filters('Elderbraum/WooProductSet/models/store-children', $data, $this->get_id());
        $this->update_meta_data(self::K_CHILD_PRODUCT, $data);
        do_action('Elderbraum/WooProductSet/models/store-children/post', $data, $this->get_id());
    }
}
