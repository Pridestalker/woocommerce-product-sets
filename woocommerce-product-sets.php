<?php

/**
 * Plugin Name:         Woocommerce product set
 * Version:             2.0.5
 * Description:         A plugin adding a new product-type
 * Author:              Elderbraum
 * License:             GPL-2.0+
 * License URI:         http://www.gnu.org/licenses/gpl-2.0.txt
 * Requires at least:   5.7
 * Requires PHP:        7.4
 * Text Domain:         woocommerce-product-sets
 * Domain Path:         /resources/lang
 */

defined( 'WPINC' ) || die;

const WOO_PROD_SETS_VERSION = '2.0.5';
const WOO_PROD_SETS_FILE    = __FILE__;
const WOO_PROD_SETS_DIR     = __DIR__;

require_once __DIR__ . '/vendor/autoload.php';

add_action( 'woocommerce_loaded', [ \WooProductSets\Plugin::class, 'get_instance' ] );
