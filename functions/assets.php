<?php
/**
 * Asset functions
 *
 * @package hameslack
 */

/**
 * Return asset URL
 *
 * @package hameslack
 * @since 1.0.0
 * @return string
 */
function hameslack_asset_url() {
	return untrailingslashit( plugin_dir_url( __DIR__ ) ) . '/assets';
}

/**
 * Register all assets in wp-dependencies.json
 *
 * @return void
 */
function hameslack_register_assets() {
	$path = __DIR__ . '/../wp-dependencies.json';
	if ( ! file_exists( $path ) ) {
		return;
	}
	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$deps = json_decode( file_get_contents( $path ), true );
	if ( empty( $deps ) ) {
		return;
	}
	foreach ( $deps as $dep ) {
		if ( empty( $dep['path'] ) ) {
			continue;
		}
		$url = plugin_dir_url( __DIR__ . '/assets' ) . $dep['path'];
		switch ( $dep['ext'] ) {
			case 'css':
				wp_register_style( $dep['handle'], $url, $dep['deps'], $dep['hash'], $dep['media'] );
				break;
			case 'js':
				$footer = [ 'in_footer' => $dep['footer'] ];
				if ( in_array( $dep['strategy'], [ 'defer', 'async' ], true ) ) {
					$footer['strategy'] = $dep['strategy'];
				}
				wp_register_script( $dep['handle'], $url, $dep['deps'], $dep['hash'], $footer );
				break;
		}
	}
}
