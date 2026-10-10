<?php
/**
 * PHPStan bootstrap file.
 *
 * Defines constants that are set in WordPress but not available
 * during static analysis.
 *
 * @package HameSlack
 */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/wp/' );
}

if ( ! defined( 'WPINC' ) ) {
	define( 'WPINC', 'wp-includes' );
}

if ( ! defined( 'HAMESLACK_VERSION' ) ) {
	define( 'HAMESLACK_VERSION', 'phpstan' );
}

if ( ! defined( 'HAMESLACK_ROOT_DIR' ) ) {
	define( 'HAMESLACK_ROOT_DIR', __DIR__ );
}
