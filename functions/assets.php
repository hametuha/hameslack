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
	return untrailingslashit( plugin_dir_url( __DIR__ ) ) . '/src/';
}