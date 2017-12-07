<?php
/**
 * Add slack name.
 *
 * @param array $methods
 * @return array
 */
add_filter( 'user_contactmethods', function( $methods ) {
	if ( hameslack_invite_api_token() ) {
		$methods['slack'] = __( 'Slack User Name', 'hameslack' );
	}
	return $methods;
}, 11 );

/**
 * Register rest route for invitation
 */
add_action( 'rest_api_init', function () {
	if ( ! hameslack_invite_api_token() ) {
		return;
	}
	// Register POST route.
	register_rest_route( 'hameslack/v1', 'invitation/(?P<user_id>me|\d+)/?', [
		[
			'methods' => 'POST',
			'args' => [
				'user_id' => [
					'required' => true,
					'validate_callback' => function( $var ) {
						return ( 'me' === $var ) || is_numeric( $var );
					},
				],
			],
			'permission_callback' => function( WP_REST_Request $request ) {
				switch ( $request->get_param( 'user_id' ) ) {
					case 'me':
						$can = hameslack_can_request_invitation( get_current_user_id() );
						break;
					default:
						$can = current_user_can( 'edit_users' );
						break;
				}
				/**
				 * hameslack_rest_invitation_capability
				 *
				 * Permission for invitaiton on REST API
				 *
				 * @param bool $can
				 * @param WP_REST_Request $request
				 */
				return apply_filters( 'hameslack_rest_invitation_capability', $can, $request );
			},
			'callback' => function( WP_REST_Request $request ) {
				$user_id = $request->get_param( 'user_id' );
				if ( 'me' === $user_id ) {
					$user_id = get_current_user_id();
				}
				$response = hameslack_user_invite( $user_id );
				if ( is_wp_error( $response ) ) {
					return $response;
				}
				return new WP_REST_Response( [
					'success' => true,
					'message' => __( 'Invitation mail has been sent! Please check mailbox and follow the instructions.', 'hameslack' ),
				] );
			},
		],
	] );
} );

/**
 * Display Invite buttons.
 *
 * @param WP_User $user
 */
add_action( 'show_user_profile', function( $user ) {
	if ( ! hameslack_invite_api_token() ) {
		// Do nothing.
		return;
	}
	$show_invitation = hameslack_can_request_invitation( $user->ID );
	/**
	 * hameslack_show_invitation_compoent
	 *
	 * @param bool $show_invitation
	 * @param WP_User $user
	 */
	$show_invitation = apply_filters( 'hameslack_show_invitation_compoent', $show_invitation, $user );
	if ( ! $show_invitation ) {
		// Do nothing.
		return;
	}
	wp_enqueue_script( 'hameslack-invitation-button', hameslack_asset_url() . '/js/hameslack-invite-button.js', [ 'jquery' ], HAMESLACK_VERSION, true );
	wp_localize_script(
		'hameslack-invitation-button', 'HameslackInvitation', [
		'nonce' => wp_create_nonce( 'wp_rest' ),
		'endpoint' => rest_url( '/hameslack/v1/invitation/me' ),
		'error' => __( 'Failed to send request. Pleaes try again later, or contact to admin.', 'hamail' ),
	] );
	?>
	<h3 id="hamelack-invitaion"><?php esc_html_e( 'Request to Slack', 'hameslack' ) ?></h3>
	<p class="description">
		<?php esc_html_e( 'Please request invitation to Slack by clicking the button below.', 'hameslack' ) ?>
		
		<?php if ( $requested = hameslack_requested_time( $user->ID ) ) {
			// translators: %s is requested time string.
			printf( esc_html__( 'You have already requested at %s.', 'hameslack' ), date_i18n( get_option( 'date_format' ), $requested ) );
		} ?>
	</p>
	<p>
		<button type="button" class="button hameslack-invitation-button">
			<?php if ( $requested ) : ?>
				<?php esc_html_e( 'Resend Invitation', 'hameslack' ) ?>
			<?php else : ?>
				<?php esc_html_e( 'Request Invitation', 'hameslack' ) ?>
			<?php endif; ?>
		</button>
	</p>
	<?php
}, 20 );
