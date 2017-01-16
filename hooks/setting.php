<?php
/**
 * Setting pages
 *
 * @package hameslack
 * @since 1.0.0
 */

defined( 'ABSPATH' ) or die();

/**
 * Add setting page
 */
add_action( 'admin_menu', function () {
	$title = __( 'HameSlack Setting', 'hameslack' );
	add_options_page( $title, $title, 'manage_options', 'hameslack', function () {
		?>
        <div class="wrap">

            <h2>
                <img src="<?php echo hameslack_asset_url() ?>/img/slack_monochrome_black.png" alt="Slack"
                     style="max-width: 200px; width: auto; height: auto;">
            </h2>

            <p class="description">
				<?php printf( __( 'Set up this page to enable <a href="%s">Slack</a>.', 'hameslack' ), 'https://slack.com' ) ?>
            </p>

			<?php if ( isset( $_GET['hameslack_msg'] ) ) {
				$class_name = 'updated';
				switch ( $_GET['hameslack_msg'] ) {
					case 'updated':
						$message = __( 'Option is properly updated.', 'hameslack' );
						break;
					case 'test':
						$message = __( 'Successfully posted to Slack. Open slack and check it.', 'hameslack' );
						break;
					default:
						$class_name = 'error';
						$message    = __( 'Unregistered message. Are you cheating?', 'hameslack' );
						break;
				}
				printf( '<div class="%s"><p>%s</p></div>', $class_name, esc_html( $message ) );
			} ?>

            <form action="<?php echo admin_url( 'options-general.php?page=hameslack&hameslack_action=option' ); ?>"
                  method="post">
				<?php wp_nonce_field( 'hameslack_option' ) ?>
                <table class="form-table">
                    <tr>
                        <th>
                            <label for="slack_url"><?php _e( 'Payload URL', 'hameslack' ) ?></label>
                        </th>
                        <td>
                            <input type="url" class="regular-text" name="slack_url" id="slack_url"
                                   value="<?php echo esc_attr( hameslack_payload_url( true ) ) ?>"
                                   placeholder="https://hooks.slack.com/services/long-key/another-key/very-long-key"/>
							<?php if ( defined( 'SLACK_ENDPOINT' ) ) : ?>
                                <p>
									<?php printf( __( 'You defined constant <strong>SLACK_ENDPOINT</strong>, so <code>%s</code> will be used instead.', 'hameslack' ), SLACK_ENDPOINT ); ?>
                                </p>
							<?php endif; ?>
                            <p class="description">
								<?php printf( __( 'You can get payload URL for Webhooks <a href="%s" target="_blank">here</a>.', 'hameslack' ), 'https://api.slack.com/incoming-webhooks' ); ?>
								<?php ?>
                            </p>
                            <p class="description">

                            </p>
                        </td>
                    </tr>
                </table>
				<?php submit_button() ?>
            </form>

            <hr/>

            <h2><?php _e( 'Test Connection', 'hameslack' ) ?></h2>
            <form action="<?php echo admin_url( 'options-general.php?page=hameslack&hameslack_action=test' ); ?>"
                  method="post">
				<?php wp_nonce_field( 'hameslack_test' ) ?>
                <table class="form-table">
                    <tr>
                        <th>
                            <label for="slack_test_text"><?php _e( 'Text to post', 'hameslack' ) ?></label>
                        </th>
                        <td>
                            <textarea name="slack_test_text" id="slack_test_text"
                                      style="width: 100%; box-sizing: border-box;" rows="3"></textarea>
                            <p class="description">
                                <?php printf( __( 'Message will be sent to Slack <code>%s</code>. Try and check it.', 'hameslack' ), hameslack_default_channel() ) ?>
                            </p>
                        </td>
                    </tr>
                </table>
				<?php submit_button() ?>
            </form>

            <hr/>

            <h2><?php _e( 'Flexible Usage', 'hameslack' ) ?></h2>
            <p><?php _e( 'This plugin does nothing by default. You can make slack call with <code>do_action</code> anytime you need.', 'hameslack' ) ?></p>
            <pre style="margin: 2em 0; padding: 1em; color: #000; background: #ddd;">
// <?php _e( 'Default Usage', 'hameslack' ) ?>

do_action( 'hameslack', $text, $attachments, $channel );

// <?php _e( 'Example in <code>save_post</code> hook.', 'hameslack' ) ?>

do_action(
    'hameslack',
    '@here <?php _e( 'Post is waiting review. Go to admin screen and ', 'hameslack' ) ?>',
    [
        [
            'title'       => get_the_title( $post ),
            'title_link'  => get_edit_post_link( $post->ID, 'mail' ),
            'color'       => 'danger',
        ]
    ],
    '#news'
);
</pre>
            <p>
                <?php printf( __( 'For more details and hooks, see our <a href="%s" target="_blank">documentation</a>.', 'hameslack' ), 'https://gianism.info/addon/hameslack/' ); ?>
            </p>

            <hr/>

            <h2><?php _e( 'Support', 'hameslack' ) ?></h2>
            <p>
				<?php printf( __( 'Go to <a href="%s" target="_blank">support forum</a>.', 'hameslack' ), 'https://gianism.info/addon/hameslack/' ); ?>
            </p>
        </div>
		<?php
	} );
} );

/**
 * Save setting and test connection
 */
add_action( 'admin_init', function () {
	if ( ! isset( $_GET['hameslack_action'], $_POST['_wpnonce'] ) ) {
		// Do nothing.
		return;
	}
	try {
		switch ( $_GET['hameslack_action'] ) {
			case 'option':
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'hameslack_option' ) ) {
					throw new Exception( __( 'Nonce is wrong access.', 'hameslack' ), 401 );
				}
				update_option( 'hameslack_payload_url', $_POST['slack_url'] );
				wp_safe_redirect( admin_url( 'options-general.php?page=hameslack&hameslack_msg=updated' ) );
				exit;
				break;
			case 'test':
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'hameslack_test' ) ) {
					throw new Exception( __( 'Nonce is wrong access.', 'hameslack' ), 401 );
				}
				if ( ! $_POST['slack_test_text'] ) {
					throw new Exception( __( 'Text is not set.', 'hameslack' ), 400 );
				}
				$result = hameslack_post( $_POST['slack_test_text'] );
				if ( is_wp_error( $result ) ) {
					throw new Exception( $result->get_error_code(), $result->get_error_message() );
				}
				wp_safe_redirect( admin_url( 'options-general.php?page=hameslack&hameslack_msg=test' ) );
				exit;
				break;
			default:
				throw new Exception( __( 'Bad request.', 'hameslack' ), 400 );
				break;
		}
	} catch ( Exception $e ) {
		wp_die( $e->getMessage(), get_status_header_desc( $e->getCode() ), [
			'back_link' => true,
			'response'  => $e->getCode(),
		] );
	}
} );

/**
 * Register Slack post action
 */
add_action( 'hameslack', function ( $content, $attachment = [], $channel = '' ) {
	$result = hameslack_post( $content, $attachment, $channel );
	if ( is_wp_error( $result ) ) {
		/**
		 * hameslack_error
		 *
		 * Executed if post to slack is failed.
		 *
		 * @action hameslack_error
		 * @package hameslack
		 * @since 1.0.0
		 *
		 * @param WP_Error $error Error object.
		 */
		do_action( 'hameslack_error', $result );
	}
}, 10, 3 );

// Show message if option is not set.
add_action( 'admin_notices', function () {
	if ( ! hameslack_payload_url() && current_user_can( 'manage_options' ) ) {
		printf( '<div class="error"><p><strong>[HameSlack]</strong> %s</p></div>', sprintf( __( 'You should set up plugin <a href="%s">here</a>.', 'hameslack' ), admin_url( 'options-general.php?page=hameslack' ) ) );
	}
} );
