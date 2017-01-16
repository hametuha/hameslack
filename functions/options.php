<?php

/**
 * Get Payload URL of Slack.
 *
 * @package hameslack
 * @since 1.0.0
 * @param bool $raw If set to true, return option value with no filter.
 * @return string
 */
function hameslack_payload_url( $raw = false ) {
	$url = get_option( 'hameslack_payload_url', '' );
	if ( $raw ) {
		return $url;
	}
	if ( defined( 'SLACK_ENDPOINT' ) ) {
		$url = SLACK_ENDPOINT;
	}

	/**
	 * hameslack_payload_url
	 *
	 * The Payload URL of Slack.
	 *
	 * @package hameslack
	 * @since 1.0.0
	 * @filter hameslack_payload_url
	 * @param string $url
	 * @return string
	 */
	return apply_filters( 'hameslack_payload_url', $url );
}

/**
 * Get default channel.
 *
 * @package hameslack
 * @since 1.0.0
 * @return string
 */
function hameslack_default_channel() {
	/**
	 * hameslack_default_channel
	 *
	 * @package hameslack
	 * @since 1.0.0
	 * @filter hameslack_default_channel
	 * @param string $channel Default is '#general'
	 * @return string
	 */
	return apply_filters( 'hameslack_default_channel', '#general' );
}

/**
 * Check if this is debug environment
 *
 * @package hameslack
 * @since 1.0.0
 * @return bool
 */
function hameslack_is_debug() {
	/**
	 * hameslack_is_debug
	 *
	 * Check if this is debug environment
	 *
	 * @package hameslack
	 * @since 1.0.0
	 * @filter hameslack_is_debug
	 * @param bool $is_debug
	 * @return bool
	 */
	return apply_filters( 'hameslack_is_debug', ( defined( 'WP_DEBUG' ) && WP_DEBUG ) );
}
