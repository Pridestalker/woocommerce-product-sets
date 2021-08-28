<?php

namespace WooProductSets\Hooks\Actions\WooCommerce;

use WooProductSets\Hooks\Hook;

class SaveChildProducts extends Hook {
	public static function hook_name(): string {
		return 'post_updated';
	}

	public function hook( $post_id ) {
		$product = wc_get_product( $post_id );

		if ( ! $product || $product->get_type() !== 'set' ) {
			return;
		}

		if ( ! isset( $_POST['child_products'] ) ) {
			return;
		}

		$children = $_POST['child_products'];
		$product->set_meta_data( $children );
		$product->save_meta_data();
	}
}
