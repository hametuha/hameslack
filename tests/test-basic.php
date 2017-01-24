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
	 * Post
	 */
	public function test_default() {
		$bot = hameslack_bot_request( 'GET', 'channels.history' );
		$this->assertWPError( $bot );
	}
}
