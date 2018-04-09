<?php
/*
Plugin Name: HameSlack
Plugin URI: https://wordpress.org/extend/plugins/hameslack/
Description: A WordPress utility for Slack.
Author: Takahashi_Fumiki
Version: 1.1.1
PHP Version: 5.4
Author URI: https://gianism.info/add-on/hameslack/
License: GPL3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Text Domain: hameslack
Domain Path: /languages
*/

defined( 'ABSPATH' ) or die();

// Get version number
$info = get_file_data( __FILE__, array(
	'version' => 'Version',
	'php_version' => 'PHP Version',
	'domain' => 'Text Domain',
) );

load_plugin_textdomain( $info['domain'], true, basename( __DIR__ ) . '/languages' );

define( 'HAMESLACK_VERSION', $info['version'] );

try {
	if ( version_compare( phpversion(), $info['php_version'], '<' ) ) {
		throw new Exception( sprintf( __( '[Hameslack] Sorry, this plugin requires PHP %s and over, but your PHP is %s.', 'hameslack' ), $info['php_version'], phpversion() ) );
	}
	// Load functions
	foreach ( array( 'functions', 'hooks' ) as $dir_name ) {
		$dir = __DIR__.'/'.$dir_name.'/';
		foreach ( scandir( $dir ) as $file ) {
			if ( preg_match( '#^[^.](.*)\.php$#u', $file ) ) {
				require $dir.$file;
			}
		}
	}
} catch ( Exception $e ) {
	$error = sprintf( '<div class="error"><p>%s</p></div>', $e->getMessage() );
	add_action( 'admin_notices', create_function( '', sprintf( 'echo \'%s\';', str_replace( '\'', '\\\'', $error ) ) ) );
}
