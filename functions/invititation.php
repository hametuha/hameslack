<?php
/**
 * Invitation related functions.
 *
 * @package hamail
 * @since 1.1.0
 */

/**
 * User can request invitation?
 *
 * @param int $user_id
 * @return bool
 */
function hameslack_can_request_invitation( $user_id ) {
	$can = user_can( $user_id, 'read' );
	/**
	 * hameslack_user_can_request_invitation
	 *
	 * @param bool $can
	 * @param int  $user_id
	 * @return bool
	 */
	return apply_filters( 'hameslack_user_can_request_invitation', $can, $user_id );
}

/**
 * A channel name to report intivation request.
 *
 * @return string
 */
function hameslack_user_invitation_channel() {
	return get_option( 'hameslack_invitation_channel', '' );
}

/**
 * Invite user.
 *
 * Invitation API is now only valid for Enterprise Grid.
 *
 * @see https://api.slack.com/methods/admin.users.invite
 * @since 1.1.0
 * @param int $user_id
 * @return stdClass|WP_Error
 */
function hameslack_user_invite( $user_id ) {
	try {
		$channel = hameslack_user_invitation_channel();
		if ( ! $channel ) {
			throw new Exception( __( 'API to invite user is not activated.', 'hameslack' ), 401 );
		}
		$user = get_user_by( 'id', $user_id );
		if ( ! $user ) {
			throw new Exception( __( 'Specified user doesn\'t exist.', 'hameslack' ), 404 );
		}
		// translators: %d is ID, %s is user_login.
		$title       = sprintf( __( 'New user #%d %s request invitation', 'hametuha' ), $user->ID, $user->user_login );
		$attachments = [ [
			'fallback'    => $title,
			'title'       => $title,
			'title_link'  => admin_url( 'user-edit.php?user_id=' . $user->ID ),
			'author_name' => $user->display_name,
			'author_link' => admin_url( 'user-edit.php?user_id=' . $user->ID ),
			'text'        => $user->user_email,
			'color'       => 'good',
		] ];
		/**
		 * hameslack_invite_args
		 *
		 * @param array   $attachments Associative array.
		 * @param WP_User $user
		 */
		$attachments = apply_filters( 'hameslack_invite_args', $attachments, $user );
		$response    = hameslack_post( $title, $attachments, $channel );
		if ( is_wp_error( $response ) ) {
			return $response;
		}
		// phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
		update_user_meta( $user_id, 'hameslack_last_requested', current_time( 'timestamp' ) );
		return $response;
	} catch ( Exception $e ) {
		return new WP_Error( 'hameslack_invite_error', $e->getMessage(), [
			'status' => $e->getCode(),
		] );
	}  // End try().
}

/**
 * Last requested timestamp
 *
 * @param int $user_id
 * @return int
 */
function hameslack_requested_time( $user_id ) {
	return (int) get_user_meta( $user_id, 'hameslack_last_requested', true );
}
