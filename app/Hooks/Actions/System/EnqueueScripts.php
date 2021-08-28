<?php

namespace WooProductSets\Hooks\Actions\System;

use WooProductSets\Hooks\Hook;

class EnqueueScripts extends Hook {
	public static function hook_name() {
		return 'admin_footer';
	}

	public function hook() {
		wp_enqueue_script(
			'woocommerce-product-sets-main',
			plugin_dir_url( WOO_PROD_SETS_FILE ) . '/dist/scripts/app.js',
			[ 'jquery' ],
			filemtime( WOO_PROD_SETS_DIR . '/dist/scripts/app.js' ),
			true
		);
	}
}
