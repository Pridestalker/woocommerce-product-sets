<?php

namespace WooProductSets\Hooks\Actions\System;

use WooProductSets\Hooks\Hook;

/**
 * This action is responsible for the installation
 * of the plugins. It handles the creation of all
 * necessities.
 *
 * @version 1.0.0
 * @author Mitch Hijlkema <mitch@mitchhijlkema.nl>
 * @copyright 2021 Mitch Hijlkema
 */
class Install extends Hook {
	public static function hook_name(): string {
		return 'woo_product_sets_activation';
	}

	public function hook() {
		$this->createTaxonomyIfNotExists();
		$this->correctProductTypes();
	}

	private function createTaxonomyIfNotExists() {
		if ( ! get_term_by( 'slug', 'set', 'product_type' ) ) {
			wp_insert_term( 'set', 'product_type' );
		}
	}

	public function correctProductTypes() {
		$products = get_posts( [
			'post_type'   => 'product',
			'numberposts' => - 1,
			'fields'      => 'ids',
			'meta_query'  => [
				[
					'key'     => '_set_price',
					'compare' => 'EXISTS',
				]
			]
		] );

		foreach ( $products as $product ) {
			wp_set_object_terms( $product, 'set', 'product_type', false );
		}
	}
}
