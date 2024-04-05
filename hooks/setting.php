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
				<img src="<?php echo hameslack_asset_url(); ?>/img/slack_monochrome_black.png" alt="Slack"
					style="max-width: 200px; width: auto; height: auto;">
			</h2>

			<p class="description">
				<?php
				// translators: %s is URL.
				printf( __( 'Set up this page to enable <a href="%s">Slack</a>.', 'hameslack' ), 'https://slack.com' );
				?>
			</p>

			<?php
			if ( isset( $_GET['hameslack_msg'] ) ) {
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
			}
			?>

			<form method="POST" action="<?php echo admin_url( 'options.php' ); ?>">
				<?php
				settings_fields( 'hameslack' );
				do_settings_sections( 'hameslack' );
				submit_button();
				?>
			</form>

			<hr/>

			<h2><?php esc_html_e( 'Test Connection', 'hameslack' ); ?></h2>
			<form action="<?php echo admin_url( 'options-general.php?page=hameslack&hameslack_action=test' ); ?>"
				method="post">
				<?php wp_nonce_field( 'hameslack_test' ); ?>
				<table class="form-table">
					<tr>
						<th>
							<label for="slack_test_text"><?php _e( 'Text to post', 'hameslack' ); ?></label>
						</th>
						<td>
							<textarea name="slack_test_text" id="slack_test_text"
								style="width: 100%; box-sizing: border-box;" rows="3"></textarea>
							<p class="description">
								<?php
								// translators: %s is default channel.
								printf( __( 'Message will be sent to Slack <code>%s</code>. Try and check it.', 'hameslack' ), hameslack_default_channel() );
								?>
							</p>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>

			<hr/>

			<h2><?php esc_html_e( 'How to Use', 'hameslack' ); ?></h2>
			<p>
				<?php esc_html_e( 'This plugin does nothing by default. ', 'hameslack' ); ?>
			</p>
			<p>
				<?php
				// translators: %s is link to documentation.
				printf( __( 'For more details and hooks, see our <a href="%s" target="_blank">documentation</a>.', 'hameslack' ), 'https://gianism.info/addon/hameslack/' );
				?>
			</p>
		</div>
		<?php
	} );
} );

/**
 * Register settings API.
 */
add_action( 'admin_init', function() {
	add_settings_section( 'hameslack-credentials', __( 'Slack Credentials', 'hameslack' ), function () {
		printf( '<p class="description">%s</p>', __( 'Credential information for Slack Bot.', 'hameslack' ) );
	}, 'hameslack' );
	// Register settings.
	add_settings_field( 'hameslack_payload_url', __( 'Payload URL', 'hameslack' ), function () {
		?>
		<input type="url" class="regular-text" style="width:100%; box-sizing: border-box;" name="hameslack_payload_url"
			value="<?php echo esc_attr( hameslack_payload_url( true ) ); ?>"
			placeholder="https://hooks.slack.com/services/long-key/another-key/very-long-key"/>
		<?php if ( defined( 'SLACK_ENDPOINT' ) ) : ?>
			<p>
				<?php
				// translators: %1$s is constant name, %2$s is constant value.
				printf( __( 'You defined constant <strong>%1$s</strong>, so <code>%2$s</code> will be used instead.', 'hameslack' ), 'SLACK_ENDPOINT', SLACK_ENDPOINT );
				?>
			</p>
		<?php endif; ?>
		<p class="description">
			<?php
			// translators: %s is link to payload url.
			printf( __( 'You can get payload URL for Webhooks <a href="%s" target="_blank">here</a>.', 'hameslack' ), 'https://api.slack.com/incoming-webhooks' );
			?>
		</p>
		<?php
	}, 'hameslack', 'hameslack-credentials' );
	register_setting( 'hameslack', 'hameslack_payload_url' );

	add_settings_field( 'hameslack_outgoing', __( 'Outgoing Webhook', 'hameslack' ), function () {
		?>
		<label>
			<input type="checkbox" value="1" name="hameslack_outgoing"
				id="hameslack_outgoing" <?php checked( hameslack_use_outgoing( true ), '1' ); ?> />
			<?php esc_html_e( 'Use outgoing web hook', 'hameslack' ); ?>
		</label>
		<?php if ( defined( 'SLACK_USE_OUTGOING' ) ) : ?>
			<p>
				<?php
				// translators: %s is constant value.
				printf( __( 'You defined constant <strong>SLACK_USE_OUTGOING</strong>, so always %s.', 'hameslack' ), SLACK_USE_OUTGOING ? 'ON' : 'OFF' );
				?>
			</p>
		<?php endif; ?>
		<p class="description">
			<strong>NOTICE: </strong>
			<?php
			// translators: %s is link to outgoing webhook.
			printf( __( 'This feature will be deprecated, beacause Slack recommends using <a href="%s" target="_blank">Events API</a>.', 'hameslack' ), 'https://api.slack.com/apis/connections/events-api' );
			?>
		</p>
		<?php
	}, 'hameslack', 'hameslack-credentials' );
	register_setting( 'hameslack', 'hameslack_outgoing' );

	add_settings_field( 'hameslack_bot_key', __( 'Bot Token', 'hameslack' ), function () {
		?>
		<input type="text" class="regular-text" style="width:100%; box-sizing: border-box;" name="hameslack_bot_key"
			value="<?php echo esc_attr( hameslack_bot_key( true ) ); ?>"
			placeholder="xoxb-numbers-longnubmer-allnumslike092abd23"/>
		<?php if ( defined( 'SLACK_BOT_KEY' ) ) : ?>
			<p>
				<?php
				// translators: %1$s is constant name, %2$s is constant value.
				printf( __( 'You defined constant <strong>%1$s</strong>, so <code>%2$s</code> will be used instead.', 'hameslack' ), 'SLACK_BOT_KEY', SLACK_BOT_KEY );
				?>
			</p>
		<?php endif; ?>
		<p class="description">
			<?php
			// translators: %s is link to bot api key.
			printf( __( 'You can get bot token by creating <a href="%s" target="_blank">a new app</a> and install it to your workspace.', 'hameslack' ), 'https://api.slack.com/apps' );
			?>
		</p>
		<?php
	}, 'hameslack', 'hameslack-credentials' );
	register_setting( 'hameslack', 'hameslack_bot_key' );

	add_settings_field( 'hameslack_invitation_channel', __( 'Invitation Channel', 'hameslack' ), function () {
		?>
		<input type="text" class="regular-text" name="hameslack_invitation_channel"
			value="<?php echo esc_attr( get_option( 'hameslack_invitation_channel' ) ); ?>"
			placeholder="private-channel"/>
		<p class="description">
			<?php
			esc_html_e( 'If set, user can request an invitation and a bot will notify on this channel.', 'hameslack' );
			?>
		</p>
		<?php
	}, 'hameslack', 'hameslack-credentials' );
	register_setting( 'hameslack', 'hameslack_invitation_channel' );
}  );

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
		// translators: %s is URL.
		printf( '<div class="error"><p><strong>[HameSlack]</strong> %s</p></div>', sprintf( __( 'You should set up plugin <a href="%s">here</a>.', 'hameslack' ), admin_url( 'options-general.php?page=hameslack' ) ) );
	}
} );
