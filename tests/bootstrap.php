<?php
/**
 * PHPUnit bootstrap file
 *
 * @package HameSlack
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

// Forward custom PHPUnit Polyfills configuration to PHPUnit bootstrap file.
$_phpunit_polyfills_path = getenv( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH' );
if ( false !== $_phpunit_polyfills_path ) {
	define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', $_phpunit_polyfills_path );
}

if ( ! file_exists( "{$_tests_dir}/includes/functions.php" ) ) {
	echo "Could not find {$_tests_dir}/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once "{$_tests_dir}/includes/functions.php";

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	// Load Gianism plugin first.
	$gianism_dir = dirname( __DIR__ ) . '/vendor/plugins/gianism';
	$gianism_autoload = $gianism_dir . '/vendor/autoload.php';
	$gianism_main = $gianism_dir . '/wp-gianism.php';
	if ( file_exists( $gianism_autoload ) ) {
		require $gianism_autoload;
	}
	if ( file_exists( $gianism_main ) ) {
		require $gianism_main;
	}
	// Load HameSlack plugin.
	require dirname( __DIR__ ) . '/hameslack.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require "{$_tests_dir}/includes/bootstrap.php";
