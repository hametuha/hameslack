<?php
/*
Plugin Name: HameSlack
Plugin URI: https://wordpress.org/extend/plugins/hameslack/
Description: A WordPress utility for Slack.
Author: Takahashi_Fumiki
Version: 1.2.0
PHP Version: 5.4
Author URI: https://gianism.info/add-on/hameslack/
License: GPL3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Text Domain: hameslack
Domain Path: /languages
*/

defined( 'ABSPATH' ) or die();

// Load autoloader.
require dirname( __FILE__ ) . '/vendor/autoload.php';

/**
 * Initialize hameslack.
 *
 * @internal
 * @since 1.2.0
 */
function hameslack_initialize() {
	// Get version number
	$info = get_file_data( __FILE__, array(
		'version' => 'Version',
		'php_version' => 'PHP Version',
		'domain' => 'Text Domain',
	) );

	load_plugin_textdomain( $info['domain'], true, basename( __DIR__ ) . '/languages' );

	define( 'HAMESLACK_VERSION', $info['version'] );
	define( 'HAMESLACK_ROOT_DIR', dirname( __FILE__ ) );

	try {
		if ( version_compare( phpversion(), $info['php_version'], '<' ) ) {
			throw new Exception( sprintf( __( '[Hameslack] Sorry, this plugin requires PHP %s and over, but your PHP is %s.', 'hameslack' ), $info['php_version'], phpversion() ) );
		}
		// Register auto loader.
		require dirname( __FILE__ ) . '/vendor/autoload.php';
		// Load functions
		foreach ( array( 'functions', 'hooks' ) as $dir_name ) {
			$dir = __DIR__.'/'.$dir_name.'/';
			if ( ! is_dir( $dir ) ) {
				continue;
			}
			foreach ( scandir( $dir ) as $file ) {
				if ( preg_match( '#^[^.](.*)\.php$#u', $file ) ) {
					require $dir.$file;
				}
			}
		}
	} catch ( Exception $e ) {
		hameslack_error( $e->getMessage() );
		add_action( 'admin_notices', 'hameslack_error' );
	}
}
add_action( 'plugins_loaded', 'hameslack_initialize', 9 );


/**
 * Display or register error message.
 *
 * @internal
 * @since 1.2.0
 * @param string $message Error message to display
 */
function hameslack_error( $message = '' ) {
	static $error = '';
	if ( $message ) {
		$error = $message;
	} else {
		printf( '<div class="error"><p>%s</p></div>', esc_html( $message ) );
	}
}

/**
 * Register slack for gianism.
 *
 * @since 1.2.0
 * @param $services
 *
 * @return mixed
 */
function hameslack_register_gianism( $services ) {
	$services['slack'] = 'Hametuha\\HameSlack\\Service\\Slack';
	return $services;
}
add_filter( 'gianism_additional_service_classes', 'hameslack_register_gianism' );
