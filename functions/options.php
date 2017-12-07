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
 * API token for invite
 *
 * @return string
 */
function hameslack_invite_api_token() {
	return (string) get_option( 'hameslack_invite_api_token', '' );
}

/**
 * Whether to use outgoing webhook
 *
 * @param bool $raw
 *
 * @return bool
 */
function hameslack_use_outgoing( $raw = false ) {
	$option = get_option( 'hameslack_outgoing', false );
	if ( $raw ) {
		return $option;
	}
	if ( defined( 'SLACK_USE_OUTGOING' ) ) {
		$option = (bool) SLACK_USE_OUTGOING;
	}

	/**
	 * hameslack_use_outgoing
	 *
	 * Whether to use outgoing webhook
	 *
	 * @package hameslack
	 * @since 1.0.0
	 * @filter hameslack_use_outgoing
	 * @param bool $use
	 * @return bool
	 */
	return apply_filters( 'hameslack_use_outgoing', $option );
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

/**
 * Get bot key
 *
 * @param bool $raw
 *
 * @return mixed|void
 */
function hameslack_bot_key( $raw = false ) {
	$key = get_option( 'hameslack_bot_key', '' );
	if ( $raw ) {
		return $key;
	}
	if ( defined( 'SLACK_BOT_KEY' ) ) {
		$key = SLACK_BOT_KEY;
	}

	/**
	 * hameslack_bot_key
	 *
	 * Bot key
	 *
	 * @package hameslack
	 * @since 1.0.0
	 * @filter hameslack_bot_key
	 * @param string $key
	 * @return string
	 */
	return apply_filters( 'hameslack_bot_key', $key );
}

