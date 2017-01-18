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

/**
 * Send request to Slack API
 *
 * @package hameslack
 * @since 1.0.0
 * @param string $method
 * @param string $endpoint
 * @param array $params
 *
 * @return object|WP_Error
 */
function hameslack_bot_request( $method, $endpoint, $params = [] ) {
	$method = strtolower( $method );
	$endpoint = 'https://slack.com/api/' . trim( $endpoint, '/' );
	if ( ! ( $token = hameslack_bot_key() ) ) {
		return new WP_Error( 400, __( 'Token is required.', 'hameslack' ) );
	}
	$params = array_merge( [ 'token' => $token ], $params );
	$params_escaped = [];
	foreach ( $params as $key => $value ) {
		$params_escaped[ $key ] = rawurlencode( $value );
	}
	// Build curl options
	$options = [
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_TIMEOUT        => 5,
	];

	switch ( $method ) {
		case 'get':
			$options[ CURLOPT_URL ] = add_query_arg( $params_escaped, $endpoint );
			break;
		case 'post':
			$options[ CURLOPT_URL ] = $endpoint;
			$options[ CURLOPT_POST ] = true;
			$options[ CURLOPT_POSTFIELDS ] = $params_escaped;
			break;
		default:
			return new WP_Error( 400, __( 'Only GET or POST is allowed.', 'hameslack' ) );
			break;
	}
	$ch = curl_init();
	curl_setopt_array( $ch, $options );
	$result = curl_exec( $ch );
	if ( ! $result ) {
		$err = curl_error( $ch );
		$no  = curl_errno( $ch );
		curl_close( $ch );
		return new WP_Error( 500, sprintf( 'SLACK BOT API ERR: %s %s', $no, $err ) );
	}
	curl_close( $ch );
	$response = json_decode( $result );
	if ( ! $response ) {
		return new WP_Error( 500, __( 'Failed to parse response. something might be wrong.', 'hameslack' ) );
	}
	if ( ! $response->ok ) {
		return new WP_Error( 500, $response->error );
	}
	return $response;
}

/**
 * Get user object on slack
 *
 * @package hameslack
 * @since 1.0.0
 * @param array $names Array of slack name of users.
 *
 * @return array|WP_Error
 */
function hameslack_members( $names = [] ) {
	$response = hameslack_bot_request( 'GET', 'users.list' );
	if ( is_wp_error( $response ) ) {
		return $response;
	}
	if ( $names ) {
		$users = array_filter( $response->members, function( $member ) use ( $names ) {
			return false !== array_search( $member->name, $names );
		} );
	} else {
		$users = $response->members;
	}
	return $users;
}

/**
 * Get channel id of Slack by name.
 *
 * @package hameslack
 * @since 1.0.0
 * @param string $channel_label
 *
 * @return string|WP_Error
 */
function hameslack_channel_id( $channel_label ) {
	$channel_label = trim( $channel_label, '#' );
	$response = hameslack_bot_request( 'GET', 'channels.list' );
	if ( is_wp_error( $response ) ) {
		return $response;
	}
	foreach ( $response->channels as $channel ) {
		if ( $channel_label == $channel->name ) {
			return $channel->id;
		}
	}
	return new WP_Error( 404, sprintf( __( 'Channel %s not found.', 'hameslack' ), $channel_label ) );
}

/**
 * Get channel messages.
 *
 * @package hameslack
 * @since 1.0.0
 * @param string $channel
 * @param int $oldest Timestamp. Default is oldest.
 * @param int $latest Timestamp. Default is now.
 * @param array $args Arguments to pass. see https://api.slack.com/methods/channels.history
 *
 * @return object|string|WP_Error
 */
function hameslack_channel_history( $channel, $oldest = -1, $latest = -1, $args = [] ) {
	$channel_id = hameslack_channel_id( $channel );
	if ( is_wp_error( $channel_id ) ) {
		return $channel_id;
	}
	if ( 0 > $oldest ) {
		$oldest = 0;
	}
	if ( 0 > $latest ) {
		$latest = current_time( 'timestamp' );
	}
	$args = wp_parse_args( [
		'channel' => $channel_id,
		'latest'  => $latest,
		'oldest'  => $oldest,
	], $args );
	$response = hameslack_bot_request( 'GET', 'channels.history', $args );
	return is_wp_error( $response ) ? $response : $response->messages ;
}
