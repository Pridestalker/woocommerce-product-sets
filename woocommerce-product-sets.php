<?php

/**
 * Plugin Name: Woocommerce product set
 * Version:     1.0.0
 * Description: A plugin adding a new product-type
 * Author:      Elderbraum
 * Text Domain: woo-prod-set
 * License:     MIT
 * Requires at least: 5.4
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit(0);
}
const WOOPRODSET_FILE = WOOPRODSET_FILE?? __FILE__;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/WooProductSetMain.php';

register_activation_hook(__FILE__, [\Elderbraum\WooProductSet\Installer::class, 'install']);

add_action('woocommerce_loaded', [\Elderbraum\WooProductSet\WooProductSetMain::class, 'load']);
