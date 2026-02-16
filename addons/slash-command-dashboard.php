<?php
/**
 * Addon: Slash Command Dashboard
 *
 * Receives Slack slash commands and displays messages in WordPress dashboard widget.
 * This is a sample addon demonstrating the Bot Token feature.
 *
 * @package hameslack
 * @since 3.0.0
 */

defined( 'ABSPATH' ) or die();

// Register this addon (always runs).
add_filter( 'hameslack_addons', function ( $addons ) {
	$addons[] = [
		'id'          => 'slash-command-dashboard',
		'label'       => __( 'Slash Command Dashboard', 'hameslack' ),
		'description' => __( 'Receive Slack slash commands and show messages on the WordPress dashboard.', 'hameslack' ),
	];
	return $addons;
} );

// Stop here if not active.
if ( ! function_exists( 'hameslack_is_addon_active' ) || ! hameslack_is_addon_active( 'slash-command-dashboard' ) ) {
	return;
}

// Register settings.
add_action( 'admin_init', function () {
	add_settings_section( 'hameslack-addon-slash-command', __( 'Slash Command Dashboard', 'hameslack' ), function () {
		printf( '<p class="description">%s</p>', __( 'Settings for the Slash Command Dashboard addon.', 'hameslack' ) );
	}, 'hameslack' );

	add_settings_field( 'hameslack_slash_signing_secret', __( 'Signing Secret', 'hameslack' ), function () {
		$value = get_option( 'hameslack_slash_signing_secret', '' );
		printf(
			'<input type="text" class="regular-text" style="width:100%%; box-sizing: border-box;" name="hameslack_slash_signing_secret" value="%s" placeholder="Slack App Signing Secret" />',
			esc_attr( $value )
		);
		printf( '<p class="description">%s</p>', __( 'Signing Secret from your Slack App settings (Basic Information > App Credentials).', 'hameslack' ) );
	}, 'hameslack', 'hameslack-addon-slash-command' );

	register_setting( 'hameslack', 'hameslack_slash_signing_secret' );

	add_settings_field( 'hameslack_slash_request_url', __( 'Request URL', 'hameslack' ), function () {
		$url = rest_url( 'hameslack/v1/slash-command' );
		printf(
			'<input type="text" class="regular-text" style="width:100%%; box-sizing: border-box;" value="%s" readonly onclick="this.select();" />',
			esc_attr( $url )
		);
		printf( '<p class="description">%s</p>', __( 'Set this URL as the Request URL in your Slack App slash command configuration.', 'hameslack' ) );
	}, 'hameslack', 'hameslack-addon-slash-command' );
} );

// Register REST endpoint for slash commands.
add_action( 'rest_api_init', function () {
	register_rest_route( 'hameslack/v1', '/slash-command', [
		'methods'             => 'POST',
		'permission_callback' => '__return_true',
		'callback'            => 'hameslack_slash_command_callback',
	] );
} );

/**
 * Handle Slack slash command request.
 *
 * @param WP_REST_Request $request REST request.
 * @return WP_REST_Response|WP_Error
 */
function hameslack_slash_command_callback( $request ) {
	// Verify Slack signature.
	$signing_secret = get_option( 'hameslack_slash_signing_secret', '' );
	if ( empty( $signing_secret ) ) {
		return new WP_Error( 'hameslack_no_secret', __( 'Signing secret not configured.', 'hameslack' ), [ 'status' => 500 ] );
	}

	$timestamp = $request->get_header( 'X-Slack-Request-Timestamp' );
	$signature = $request->get_header( 'X-Slack-Signature' );
	if ( ! $timestamp || ! $signature ) {
		return new WP_Error( 'hameslack_missing_headers', __( 'Missing Slack headers.', 'hameslack' ), [ 'status' => 400 ] );
	}

	// Reject requests older than 5 minutes.
	if ( abs( time() - (int) $timestamp ) > 300 ) {
		return new WP_Error( 'hameslack_timestamp_expired', __( 'Request timestamp expired.', 'hameslack' ), [ 'status' => 403 ] );
	}

	// Verify HMAC-SHA256 signature.
	$body     = $request->get_body();
	$sig_base = 'v0:' . $timestamp . ':' . $body;
	$expected = 'v0=' . hash_hmac( 'sha256', $sig_base, $signing_secret );
	if ( ! hash_equals( $expected, $signature ) ) {
		return new WP_Error( 'hameslack_invalid_signature', __( 'Invalid signature.', 'hameslack' ), [ 'status' => 403 ] );
	}

	// Extract message from the command.
	$text    = sanitize_text_field( $request->get_param( 'text' ) );
	$user    = sanitize_text_field( $request->get_param( 'user_name' ) );
	$user_id = sanitize_text_field( $request->get_param( 'user_id' ) );
	$channel = sanitize_text_field( $request->get_param( 'channel_name' ) );

	if ( empty( $text ) ) {
		return new WP_REST_Response( [
			'response_type' => 'ephemeral',
			'text'          => __( 'Please provide a message. Usage: /hameslack your message here', 'hameslack' ),
		] );
	}

	// Save message to queue (FIFO, max 10).
	$messages   = get_option( 'hameslack_slash_messages', [] );
	$messages[] = [
		'text'      => $text,
		'user'      => $user,
		'user_id'   => $user_id,
		'channel'   => $channel,
		'timestamp' => current_time( 'mysql' ),
	];
	// Keep only the latest 10 messages.
	if ( count( $messages ) > 10 ) {
		$messages = array_slice( $messages, -10 );
	}
	update_option( 'hameslack_slash_messages', $messages );

	// Send DM confirmation via Bot Token.
	if ( $user_id && hameslack_bot_key() ) {
		hameslack_bot_request( 'POST', 'chat.postMessage', [
			'channel' => $user_id,
			// translators: %s is the message text.
			'text'    => sprintf( __( 'Your message has been sent to the WordPress dashboard: "%s"', 'hameslack' ), $text ),
		] );
	}

	return new WP_REST_Response( [
		'response_type' => 'ephemeral',
		// translators: %s is the site name.
		'text'          => sprintf( __( 'Message sent to %s dashboard!', 'hameslack' ), get_bloginfo( 'name' ) ),
	] );
}

// Register dashboard widget.
add_action( 'wp_dashboard_setup', function () {
	wp_add_dashboard_widget(
		'hameslack_slash_messages',
		__( 'Slack Messages', 'hameslack' ),
		'hameslack_slash_dashboard_widget'
	);
} );

/**
 * Render the dashboard widget.
 */
function hameslack_slash_dashboard_widget() {
	$messages = get_option( 'hameslack_slash_messages', [] );
	if ( empty( $messages ) ) {
		printf( '<p>%s</p>', esc_html__( 'No messages received yet.', 'hameslack' ) );
		return;
	}
	// Display in reverse chronological order.
	$messages = array_reverse( $messages );
	echo '<ul style="margin: 0;">';
	foreach ( $messages as $msg ) {
		printf(
			'<li style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>@%s</strong> <small>(%s)</small><br />%s</li>',
			esc_html( $msg['user'] ),
			esc_html( $msg['timestamp'] ),
			esc_html( $msg['text'] )
		);
	}
	echo '</ul>';
}
