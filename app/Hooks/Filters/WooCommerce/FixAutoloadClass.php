<?php

namespace WooProductSets\Hooks\Filters\WooCommerce;

use WooProductSets\Hooks\Hook;
use WooProductSets\Models\WC_Product_Set;

class FixAutoloadClass extends Hook {
	public static function hook_name() {
		return 'woocommerce_product_class';
	}

	public static function parameter_count(): int {
		return 2;
	}

	public function hook( string $className, string $product_type ): string {
		if ( $product_type === 'set' ) {
			return WC_Product_Set::class;
		}

		return $className;
	}
}
