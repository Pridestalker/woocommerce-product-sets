<?php

namespace WooProductSets\System;

defined( 'ABSPATH' ) || exit( 0 );

use DI\Container as IoContainer;
use DI\ContainerBuilder;

/**
 * Service container for your plugin.
 * Class Container
 * @package WooProductSets\System
 */
class Container {
	protected static ?Container $_instance = null;
	protected static ?IoContainer $_container = null;

	private function __construct() {
		if ( static::$_container === null ) {
			$builder = new ContainerBuilder();
			$builder->addDefinitions( WOO_PROD_SETS_DIR . '/config/wires.php' );
			$builder->enableCompilation( WOO_PROD_SETS_DIR . '/storage/cache/' );
			static::$_container = $builder->build();
		}
	}

	public static function get( $abstract ) {
		static::bootstrap();

		return static::$_container->get( $abstract );
	}

	public static function bootstrap(): self {
		if ( static::$_instance === null ) {
			static::$_instance = new self();
		}

		return static::$_instance;
	}

	public static function make( $abstract, $parameters = [] ) {
		static::bootstrap();

		return static::$_container->make( $abstract, $parameters );
	}

}
