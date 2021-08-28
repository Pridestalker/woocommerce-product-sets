<?php

namespace WooProductSets;

use WooProductSets\System\Container;

/**
 * Gets the service container, or a class by it.
 *
 * @return mixed
 */
function app( $abstract = null, $parameters = [] ) {
	if ( is_null( $abstract ) ) {
		return Container::bootstrap();
	}

	return Container::make( $abstract, $parameters );
}

/**
 * @param null $key
 * @param null $default
 *
 * @return mixed
 */
function config( $key = null, $default = null ) {
	$key = preg_split( '/(\.|\/)/', $key );

	$file = array_shift( $key );
	$file = $file . '.php';

	return app( 'config', [ 'file' => $file ] )
		->get( $key, $default );
}

/**
 * Return the default value of the given value.
 *
 * @param mixed $value
 *
 * @return mixed
 */
function value( $value, ...$args ) {
	return $value instanceof \Closure ? $value( ...$args ) : $value;
}

/**
 * @param $env
 *
 * @return bool|string
 */
function environment( ...$env ) {
	if ( count( $env ) === 0 ) {
		return config( 'app.env' );
	}

	return in_array( config( 'app.env' ), $env );
}
