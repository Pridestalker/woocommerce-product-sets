<?php

namespace Elderbraum\WooProductSet;

class UnInstaller implements Installable
{
	protected static ?UnInstaller $_instance = null;

	/**
	 * Make a class instance.
	 *
	 * @return UnInstaller
	 */
	public static function install(): UnInstaller
	{
		if ( null === static::$_instance ) {
			static::$_instance = new static();
		}

		return static::$_instance;
	}

	private function __construct() {
		$this->removeCronJob();
	}

	private function removeCronJob() {
		$timestamp = wp_next_scheduled( Cron::HOOK_NAME );
		wp_unschedule_event( $timestamp, Cron::HOOK_NAME );
	}
}
