<?php

namespace WooProductSets\System;

use League\Flysystem\Local\LocalFilesystemAdapter;
use function WooProductSets\app;

/**
 * The filesystem adapter for your plugin.
 *
 * Class FileSystem
 * @package WooProductSets\System
 */
class FileSystem {

	protected static ?FileSystem $_instance = null;

	private \League\Flysystem\Filesystem $uploadsFS;
	private \League\Flysystem\Filesystem $pluginFS;

	public function __construct() {
		$this->uploadsFS = new \League\Flysystem\Filesystem( new LocalFilesystemAdapter( WP_CONTENT_DIR . '/uploads/' ) );
		$this->pluginFS  = new \League\Flysystem\Filesystem( new LocalFilesystemAdapter( WOO_PROD_SETS_DIR ) );
	}

	public static function bootstrap(): FileSystem {
		if ( null === static::$_instance ) {
			static::$_instance = app( FileSystem::class );
		}

		return static::$_instance;
	}

	public function getJson( $file ) {
		return json_decode( $this->get( $file ) );
	}

	/**
	 * @throws \League\Flysystem\FilesystemException
	 */
	public function get( $file ) {
		return $this->uploadsFS->read( $file );
	}

	public function write( $file, $data ) {
		$this->uploadsFS->write( $file, $data );
	}

	public function hasPluginFile( $file ) {
		return $this->pluginFS->fileExists( $file );
	}

	public function getPluginFile( $file ) {
		return $this->pluginFS->read( $file );
	}
}
