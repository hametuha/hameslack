<?php
/**
 * Test Case
 *
 * @package hameslack
 */

/**
 * Sample test case.
 */
class HameSlack_Basic_Test extends WP_UnitTestCase {

	/**
	 * Class exists.
	 */
	public function test_auto_loader() {
		//$this->assertTrue( class_exists( 'Hametuha\\HameSlack\\Service\\Slack' ) );
	}

	/**
	 * Post
	 */
	public function test_default() {
		$bot = hameslack_bot_request( 'GET', 'channels.history' );
		$this->assertWPError( $bot );
	}
}
