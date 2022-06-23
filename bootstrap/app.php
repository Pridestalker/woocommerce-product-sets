<?php
/**
 * The plugin bootstrap file used by your plugin to initialize your app.
 */

Kint::$enabled_mode = wp_get_environment_type() === 'development';

\register_activation_hook( WOO_PROD_SETS_FILE,
	function () {
		do_action( 'woo_product_sets_activation' );
	} );

\register_deactivation_hook( WOO_PROD_SETS_FILE,
	function () {
		do_action( 'woo_product_sets_deactivation' );
	} );

foreach ( \WooProductSets\config( 'hooks.cron_jobs' ) as $cron ) {
	if ( ! wp_next_scheduled( $cron::hook_name() ) ) {
		wp_schedule_event( time(), $cron::recurrence(), $cron::hook_name() );
	}
}

function run_woo_product_sets() {
    \WooProductSets\Plugin::get_instance();

	do_action( 'woo_product_sets_init' );
}

run_woo_product_sets();
