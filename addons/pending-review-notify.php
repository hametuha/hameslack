<?php
/**
 * Addon: Pending Review Notify
 *
 * Sends a Slack notification when a post transitions to "pending" status.
 * This is a sample addon demonstrating the Payload URL feature.
 *
 * @package hameslack
 * @since 3.0.0
 */

defined( 'ABSPATH' ) or die();

// Register this addon (always runs).
add_filter( 'hameslack_addons', function ( $addons ) {
	$addons[] = [
		'id'          => 'pending-review-notify',
		'label'       => __( 'Pending Review Notify', 'hameslack' ),
		'description' => __( 'Notify Slack when a post is submitted for review.', 'hameslack' ),
	];
	return $addons;
} );

// Stop here if not active.
if ( ! function_exists( 'hameslack_is_addon_active' ) || ! hameslack_is_addon_active( 'pending-review-notify' ) ) {
	return;
}

// Register settings for notification channel.
add_action( 'admin_init', function () {
	add_settings_section( 'hameslack-addon-pending-review', __( 'Pending Review Notify', 'hameslack' ), function () {
		printf( '<p class="description">%s</p>', __( 'Settings for the Pending Review notification addon.', 'hameslack' ) );
	}, 'hameslack' );

	add_settings_field( 'hameslack_pending_review_channel', __( 'Notification Channel', 'hameslack' ), function () {
		$value = get_option( 'hameslack_pending_review_channel', '' );
		printf(
			'<input type="text" class="regular-text" name="hameslack_pending_review_channel" value="%s" placeholder="#general" />',
			esc_attr( $value )
		);
		printf( '<p class="description">%s</p>', __( 'Slack channel to send pending review notifications. Leave empty to use the default channel.', 'hameslack' ) );
	}, 'hameslack', 'hameslack-addon-pending-review' );

	register_setting( 'hameslack', 'hameslack_pending_review_channel' );
} );

// Notify on transition to pending status.
add_action( 'transition_post_status', function ( $new_status, $old_status, $post ) {
	if ( 'pending' !== $new_status || 'pending' === $old_status ) {
		return;
	}

	$author  = get_userdata( $post->post_author );
	$channel = get_option( 'hameslack_pending_review_channel', '' );
	// translators: %1$s is author name, %2$s is post type singular label.
	$text = sprintf(
		/* translators: %1$s is author name, %2$s is post type singular label. */
		__( 'A %2$s has been submitted for review by %1$s.', 'hameslack' ),
		$author ? $author->display_name : __( 'Unknown', 'hameslack' ),
		get_post_type_object( $post->post_type )->labels->singular_name
	);

	$attachment = [
		[
			'title'      => get_the_title( $post ),
			'title_link' => get_edit_post_link( $post->ID, 'raw' ),
			'text'       => wp_trim_words( strip_tags( $post->post_content ), 40 ),
			'fields'     => [
				[
					'title' => __( 'Post Type', 'hameslack' ),
					'value' => get_post_type_object( $post->post_type )->labels->singular_name,
					'short' => true,
				],
				[
					'title' => __( 'Author', 'hameslack' ),
					'value' => $author ? $author->display_name : __( 'Unknown', 'hameslack' ),
					'short' => true,
				],
			],
		],
	];

	/**
	 * Fires to send a message to Slack.
	 *
	 * @param string $text       Message text.
	 * @param array  $attachment Slack attachment array.
	 * @param string $channel    Slack channel.
	 */
	do_action( 'hameslack', $text, $attachment, $channel );
}, 10, 3 );
