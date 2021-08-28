<?php

namespace WooProductSets\Hooks\Actions\WooCommerce;

use WooProductSets\Hooks\Hook;

class EditStockOnOrder extends Hook {
	public static function hook_name() {
		return 'woocommerce_order_status_processing';
	}

	public function hook( $order_id ) {

		$order = wc_get_order( $order_id );


		foreach ( $order->get_items() as $item ) {
			if ( $item->get_type() !== 'line_item' ) {
				continue;
			}

			$product = $item->get_product();
			if ( $product->get_type !== 'set' ) {
				continue;
			}

			foreach ( $product->get_child_products_as_wc_product() as $childProduct ) {
				$newStock = $childProduct->get_stock_quantity( 'edit' ) - $item->get_quantity();

				/* Translators: 1: Parent product name 2: Child product name 3: Original quantity 4: New quantity */
				$order_note = sprintf(
					__( '[%1$s]: Reduced stock for %2%s from %3$s -> %4$s ', 'woocommerce-product-sets' ),
					$product->get_title(),
					$childProduct->get_title(),
					$childProduct->get_stock_quantity( 'edit' ),
					$newStock
				);

				$order->add_order_note( $order_note );
				wc_update_product_stock(
					$childProduct->get_id(),
					$newStock
				);
			}
		}
	}
}
