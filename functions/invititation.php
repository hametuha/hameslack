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
		$token = hameslack_invite_api_token();
		if ( ! $token ) {
			throw new Exception( __( 'API to invite user is not activated.', 'hameslack' ), 401 );
		}
		$user = get_user_by( 'id', $user_id );
		if ( ! $user ) {
			throw new Exception( __( 'Specified user doesn\'t exist.', 'hameslack' ), 404 );
		}
		$invite_args = [
			'token'      => $token,
			'email'      => $user->user_email,
			'first_name' => $user->first_name,
			'last_name'  => $user->last_name,
		];
		if ( hameslack_requested_time( $user_id ) ) {
			$invite_args['resend'] = true;
		}
		/**
		 * hameslack_invite_args
		 *
		 * @see https://github.com/ErikKalkoken/slackApiDoc/blob/master/users.admin.invite.md
		 * @param array   $invite_args Associative array.
		 * @param WP_User $user
		 */
		$invite_args = apply_filters( 'hameslack_invite_args', $invite_args, $user );
		$response    = hameslack_bot_request( 'POST', 'users.admin.invite', $invite_args );
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
