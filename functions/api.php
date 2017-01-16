<?php
/**
 * API functions
 *
 * @package hameslack
 * @since 1.0.0
 */

/**
 * Post message to Slack
 *
 * @package hameslack
 * @since 1.0.0
 * @param string $content String to post
 * @param array $attachment If you have attachment, pass associative array.
 * @param string $channel Default '#general'
 *
 * @return true|WP_Error
 */
function hameslack_post( $content, $attachment = [], $channel = '' ) {
	$endpoint = hameslack_payload_url();
	if ( ! $endpoint ) {
		return new WP_Error( 500, __( 'Payload URL is not set.', 'hameslack' ) );
	}
	if ( ! $channel ) {
		$channel = hameslack_default_channel();
	}
	$payload = [
		'channel' => $channel,
	];
	$debug = hameslack_is_debug();
	if ( hameslack_is_debug() ) {
		$content = "[Debug] {$content}";
	}
	$payload['text'] = $content;
	if ( $attachment ) {
		$payload['attachments'] = $attachment;
	}
	$ch = curl_init();
	curl_setopt_array( $ch, [
		CURLOPT_URL            => $endpoint,
		CURLOPT_POST           => true,
		CURLOPT_HTTPHEADER     => [ 'Content-Type: application/json' ],
		CURLOPT_POSTFIELDS     => json_encode( $payload ),
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_TIMEOUT        => 5,
	] );
	$result = curl_exec( $ch );
	if ( ! $result ) {
		$err = curl_error( $ch );
		$no  = curl_errno( $ch );
		return new WP_Error( 500, sprintf( 'SLACK API ERR: %s %s', $no, $err ) );
	} elseif ( $debug ) {
		error_log( sprintf( 'SLACK_SUCCESS: %s %s', $result, json_encode( $payload ) ) );
	}
	curl_close( $ch );

	return false !== $result;
}