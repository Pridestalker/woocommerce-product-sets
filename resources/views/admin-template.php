<div class="options_group show_if_set clear">
	<?php woocommerce_wp_select( [
		'id'      => '_price_type',
		'label'   => __( 'Price type', 'woocommerce-product-sets' ),
		'options' => [
			'fixed'              => __( 'Fixed', 'woocommerce-product-sets' ),
			'dynamic-sum'        => __( 'Dynamic', 'woocommerce-product-sets' ),
			'dynamic-percentage' => __( 'Dynamic percentage fee', 'woocommerce-product-sets' ),
		],
	] ); ?>

	<?php woocommerce_wp_text_input(
		[
			'id'          => '_set_price',
			'label'       => __( 'Set price', 'woocommerce-product-sets' ),
			'value'       => $product->get_meta( '_set_price' ),
			'data_type'   => 'price',
			'description' => __( 'This is the base price of the product. Only available when price type is set to fixed',
				'woocommerce-product-sets' ),
		]
	); ?>

	<?php woocommerce_wp_text_input(
		[
			'id'          => '_set_price_percentage_fee',
			'label'       => __( 'Set price percentage fee', 'woocommerce-product-sets' ),
			'value'       => $product->get_meta( '_set_price_percentage_fee' ) ?: 100,
			'data_type'   => 'decimal',
			'description' => __( 'Change this number if you want to edit the fee on this product. > 100 is more expensive, < 100 is percentage reduction',
				'woocommerce-product-sets' ),
		]
	); ?>

    <div class="options_group">
        <p class="form-field">
            <label for="child_products"><?= __('Child products', 'woocommerce-product-setss') ?></label>
            <select class="wc-product-search" multiple="multiple" style="width: 50%;" id="child_products"
                    name="child_products[]"
                    data-placeholder="<?= esc_attr__( 'Search for a product&hellip;', 'woocommerce' ); ?>"
                    data-action="woocommerce_json_search_products_and_variations"
                    data-exclude="<?= (int) $product->get_id(); ?>">
			    <?php
			    $product_object = $product;
			    $product_ids    = $product_object->get_child_products();

			    foreach ( $product_ids as $product_id ) {
				    $product = wc_get_product( $product_id );
				    if ( is_object( $product ) ) { ?>
                        <option value="<?= esc_attr( $product_id ); ?>" <?= selected( true, true, false ); ?>>
						    <?= htmlspecialchars( wp_kses_post( $product->get_formatted_name() ) ) ?>
                        </option>
					    <?php
				    }
			    }
			    ?>
            </select>
        </p>
    </div>
</div>
