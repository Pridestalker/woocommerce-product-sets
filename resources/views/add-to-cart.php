<?php
/**
 * Set product add to cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/add-to-cart/set.php.
 *
 * HOWEVER, on occasion we will need to update the template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes
 *
 * @version 1.0.0
 * @author M.Hijlkema
 *
 * @var \WooProductSets\Models\WC_Product_Set $product
 */

defined('ABSPATH') || exit;

if (!$product->is_purchasable()) {
    return;
}

// Add a filter to hide the stock if so required.
if (apply_filters('woo_product_sets/add_to_cart/show-stock', true, $product->get_id(), $product)) {
    echo wc_get_stock_html($product);
}

if (!$product->is_in_stock()) {
    return;
}

do_action('woocommerce_before_add_to_cart_form');
?>
    <form class="cart" action="<?= $this->esc_url(
        apply_filters(
            'woocommerce_add_to_cart_form_action',
            $product->get_permalink()
        )
    ) ?>" method="POST" enctype="multipart/form-data">
        <?php
        do_action('woocommerce_before_add_to_cart_button');

        do_action('woocommerce_before_add_to_cart_quantity');

        woocommerce_quantity_input([
            'min_value' => apply_filters('woocommerce_quantity_input_min', $product->get_min_purchase_quantity()),
            'max_value' => apply_filters('woocommerce_quantity_input_max', $product->get_max_purchase_quantity()),
            'input_value' => isset($_POST['quantity']) ? wc_stock_amount(
                wp_unslash($_POST['quantity'])
            ) : $product->get_min_purchase_quantity(),
        ]);

        do_action('woocommerce_after_add_to_cart_quantity');
        ?>

        <button type="submit" name="add-to-cart" value="<?= $this->e($product->get_id()); ?>"
                class="single_add_to_cart_button button alt"><?= $this->esc_html(
                $product->single_add_to_cart_text()
            ); ?></button>

        <?php
        do_action('woocommerce_after_add_to_cart_button'); ?>

    </form>

<?php
do_action('woocommerce_after_add_to_cart_form');

