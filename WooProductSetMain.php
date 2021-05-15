<?php

namespace Elderbraum\WooProductSet;

use App\Helpers\Log;
use Elderbraum\WooProductSet\Models\WC_Product_Set;

class WooProductSetMain
{
    protected static $_instance = null;

    public static function load()
    {
        if (static::$_instance === null) {
            static::$_instance = new static();
        }

        return static::$_instance;
    }

    public function __construct()
    {
        add_filter('product_type_selector', [$this, 'addType']);
        add_filter('woocommerce_product_class', [$this, 'fixAutoloadClass'], 10, 2);

        add_action('woocommerce_product_options_general_product_data', [$this, 'addWrapper']);
        add_action('post_updated', [$this, 'saveChildProducts']);
        add_action('post_updated', [$this, 'savePriceData'], PHP_INT_MAX);
        add_action('admin_footer', [$this, 'enqueueScripts']);

        add_action('woocommerce_order_status_processing', [$this, 'editStockOnOrder']);
    }

    /**
     * @param array $types
     *
     * @return array
     */
    public function addType(array $types): array
    {
        if (!isset($types['set'])) {
            $types['set'] = 'Product Set';
        }

        return $types;
    }

    public function editStockOnOrder($order_id)
    {
        $order = wc_get_order($order_id);

        foreach ($order->get_items() as $item) {
            if ($item->get_type() !== 'line_item') {
                continue;
            }
            $product = $item->get_product();
            if ($product->get_type() === 'set') {
                /** @var WC_Product_Set $product */

                foreach ($product->get_child_products_as_wc_product() as $childProduct) {
                    $newStock = $childProduct->get_stock_quantity('edit') - $item->get_quantity();
                    $order->add_order_note("Reduced stock for {$childProduct->get_title()} from {$childProduct->get_stock_quantity('edit')} -> {$newStock}");
                    wc_update_product_stock(
                        $childProduct->get_id(),
                        $newStock
                    );
                }
            }
        }
    }

    public function addWrapper()
    {
        /** @var WC_Product_Set $product */
        $product = wc_get_product();
        if ($product->get_type() !== 'set') {
            return;
        }
        ?>
        <div class="options_group show_if_set clear">
        <?php
        woocommerce_wp_select(
            [
                'id' => '_price_type',
                'label' => 'Price type',
                'options' => [
                    'fixed' => 'Fixed',
                    'dynamic-sum' => 'Dynamic',
                    'dynamic-percentage' => 'Dynamic percentage fee'
                ],
            ]
        );

        woocommerce_wp_text_input(
            [
                'id' => '_set_price',
                'label' => 'Set price',
                'value' => $product->get_meta('_set_price'),
                'data_type' => 'price',
                'description' => 'This is the base price of the product. Only available when price type is set to fixed'
            ]
        );

        woocommerce_wp_text_input(
            [
                'id' => '_set_price_percentage_fee',
                'label' => 'Set price percentage fee',
                'value' => $product->get_meta('_set_price_percentage_fee')?: 100,
                'data_type' => 'decimal',
                'description' => 'Change this number if you want to edit the fee on this product. > 100 is more expensive, < 100 is percentage reduction'
            ]
        );
        ?>

            <div class="options_group">
                <p class="form-field">
                    <label for="child_products">Child products</label>
                    <select class="wc-product-search" multiple="multiple" style="width: 50%;" id="child_products" name="child_products[]" data-placeholder="<?php esc_attr_e('Search for a product&hellip;', 'woocommerce'); ?>" data-action="woocommerce_json_search_products_and_variations" data-exclude="<?php echo (int) $product->get_id(); ?>">
                        <?php
                        $product_object = $product;
                        $product_ids = $product_object->get_child_products();

                        foreach ($product_ids as $product_id) {
                            $product = wc_get_product($product_id);
                            if (is_object($product)) {
                                echo '<option value="' . esc_attr($product_id) . '"' . selected(true, true, false) . '>' . htmlspecialchars(wp_kses_post($product->get_formatted_name())) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </p>
            </div>

        </div>
        <?php
    }

    public function savePriceData($post_id)
    {
        /** @var WC_Product_Set $product */
        $product = wc_get_product($post_id);
        if (!$product || $product->get_type() !== 'set') {
            return;
        }
        if (!isset($_POST['_price_type'])) {
            return;
        }

        $product->update_meta_data('_price_type', $_POST['_price_type']);
        $product->save_meta_data();

        $newPrice = 0.00;

        $product->delete_meta_data('_sale_price');
        $product->delete_meta_data('_regular_price');
        $product->delete_meta_data('_price');
        foreach ($product->get_child_products() as $childProduct) {
            $childProductTemp = wc_get_product($childProduct);
            $newPrice += $childProductTemp->get_price('edit');
        }

        $product->add_meta_data('_regular_price', $newPrice);

        switch ($product->get_meta('_price_type')) {
            case 'fixed':
                if (isset($_POST['_set_price'])) {
                    $price = sanitize_text_field($_POST['_set_price']);
                    $product->update_meta_data('_sale_price', $price);
                    $product->set_price($price);
                    $product->update_meta_data('_set_price', $price);
                }
                break;
            case 'dynamic-sum':
                if (isset($_POST['_set_products'])) {
                    $product->update_meta_data('_price', $newPrice);
                    $product->update_meta_data('_set_price', $newPrice);
                }
                break;
            case 'dynamic-percentage':
                if (isset($_POST['_set_price_percentage_fee'])) {
                    $reducedPrice = 0.00;
                    foreach ($product->get_child_products() as $id) {
                        $temp_product = wc_get_product($id);
                        $reducedPrice += $temp_product->get_price('edit');
                    }

                    $reducedPrice *= (int) $_POST['_set_price_percentage_fee'] / 100;
                    $product->update_meta_data('_sale_price', $reducedPrice);
                    $product->update_meta_data('_price', $reducedPrice);
                    $product->update_meta_data('_set_price', $reducedPrice);
                    $product->update_meta_data('_set_price_percentage_fee', $_POST['_set_price_percentage_fee']);
                }
                break;
        }
        $product->save_meta_data();
        $product->save();
        do_action('woocommerce_updated_product_price', $product->get_id());
    }

    public function saveChildProducts($post_id)
    {
        /** @var WC_Product_Set $product */
        $product = wc_get_product($post_id);

        if (!$product || $product->get_type() !== 'set') {
            return;
        }

        if (!isset($_POST['child_products'])) {
            return;
        }

        $children = $_POST['child_products'];
        $product->set_child_products($children);
        $product->save_meta_data();
    }

    public function enqueueScripts()
    {
        wp_enqueue_script(
            'woocommerce-product-sets-main',
            plugin_dir_url(WOOPRODSET_FILE) . '/dist/scripts/main.iife.js',
            ['jquery'],
            filemtime(plugin_dir_path(WOOPRODSET_FILE) . '/dist/scripts/main.iife.js'),
            true
        );
    }

    public function fixAutoloadClass(string $className, string $product_type)
    {
        if ($product_type === 'set') {
            return WC_Product_Set::class;
        }

        return $className;
    }
}

