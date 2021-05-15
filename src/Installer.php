<?php

namespace Elderbraum\WooProductSet;

class Installer implements Installable
{
	protected static ?Installer $_instance = null;

	/**
	 * Installer constructor.
	 *
	 * @private to prevent multiple instances.
	 */
	private function __construct()
	{
		$this->createTaxonomyIfNotExists();
		$this->createCronJob();
	}

	/**
	 * Creates the set product_type term in the product_type taxonomy (if it does not exist)
	 */
	private function createTaxonomyIfNotExists()
	{
		if ( ! get_term_by( 'slug', 'set', 'product_type' ) ) {
			wp_insert_term( 'set', 'product_type' );
		}
	}

	private function createCronJob()
	{
		if ( ! wp_next_scheduled( Cron::HOOK_NAME ) ) {
			wp_schedule_event( time(), 'hourly', Cron::HOOK_NAME );
		}
	}

	/**
	 * Make a class instance.
	 *
	 * @return Installer
	 */
	public static function install(): Installer
	{
		if ( null === static::$_instance ) {
			static::$_instance = new static();
		}

		return static::$_instance;
	}
}
