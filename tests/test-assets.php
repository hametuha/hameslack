<?php
/**
 * Asset registration test
 *
 * @package hameslack
 */

/**
 * Ensure every asset in wp-dependencies.json resolves to a real file.
 */
class HameSlack_Assets_Test extends WP_UnitTestCase {

	/**
	 * Registered URLs should point to existing files.
	 */
	public function test_registered_assets_resolve_to_files() {
		$root = dirname( __DIR__ );
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$deps = json_decode( file_get_contents( $root . '/wp-dependencies.json' ), true );
		$this->assertNotEmpty( $deps );
		hameslack_register_assets();
		$base_url  = plugin_dir_url( $root . '/hameslack.php' );
		$base_path = plugin_dir_path( $root . '/hameslack.php' );
		foreach ( $deps as $dep ) {
			$registered = 'css' === $dep['ext'] ? wp_styles()->registered : wp_scripts()->registered;
			$this->assertArrayHasKey( $dep['handle'], $registered );
			$src = $registered[ $dep['handle'] ]->src;
			$this->assertStringStartsWith( $base_url, $src );
			$file = $base_path . substr( $src, strlen( $base_url ) );
			$this->assertFileExists( $file, sprintf( '%s does not resolve to a file.', $src ) );
		}
	}
}
