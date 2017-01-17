<?php
/**
 * Endpoint hooks
 */

// Register post type
add_action( 'init', function () {
	if ( hameslack_use_outgoing() ) {
		$args = [
			'label'    => __( 'Slack Endpoint', 'hameslack' ),
			'public'   => false,
			'show_ui'  => true,
			'supports' => [ 'title', 'excerpt', 'slug', 'author' ],
		];
		/**
		 * hameslack_post_type_args
		 *
		 * Arguments passed to `register_post_type`
		 *
		 * @action hameslack_error
		 * @package hameslack
		 * @since 1.0.0
		 *
		 * @param array $args arguments for `register_post_type`
		 *
		 * @return array
		 */
		$args = apply_filters( 'hameslack_post_type_args', $args );
		register_post_type( 'slack-endpoint', $args );
	}
} );

// Show
add_action( 'edit_form_after_title', function ( $post ) {
	if ( 'slack-endpoint' !== $post->post_type ) {
		return;
	}
	wp_nonce_field( 'hameslack_hash', '_hameslacknonce', false );
	$hash = get_post_meta( $post->ID, '_hameslack_hash', true );
	?>
    <style type="text/css">
        .hameslack-table input[type=text] {
            box-sizing: border-box;
            width: 100%;
        }
    </style>
    <table class="form-table hameslack-table">
        <tr>
            <th>
                <label for="hameslack_hash"><?php _e( 'Hash Key', 'hameslack' ) ?></label>
            </th>
            <td>
                <input type="text" class="regular-text" name="hameslack_hash" id="hameslack_hash" readonly
                       value="<?php echo esc_attr( $hash ) ?>"
                       placeholder="<?php esc_attr_e( 'Generate automatically', 'hameslack' ) ?>"/>
            </td>
        </tr>
        <tr>
            <th>
                <label for="hameslack_endpoint"><?php _e( 'Endpoint', 'hameslack' ) ?></label>
            </th>
            <td>
				<?php if ( $hash ) : ?>
                    <input type="text" class="regular-text" id="hameslack_endpoint" readonly
                           value="<?php echo esc_attr( rest_url( "hameslack/v1/outgoing/{$hash}" ) ) ?>"/>
				<?php else : ?>
                    <p class="description">
                        <span class="dashicons dashicons-no"></span>
						<?php _e( 'Endpoint URL will be issued when you publish post.', 'hameslack' ) ?>
                    </p>
				<?php endif; ?>
            </td>
        </tr>
        <tr>
            <th>
                <label for="hameslack_token"><?php _e( 'Token', 'hameslack' ) ?></label>
            </th>
            <td>
                <input type="text" class="regular-text" name="hameslack_token" id="hameslack_token"
                       value="<?php echo esc_attr( get_post_meta( $post->ID, '_hameslack_token', true ) ) ?>"/>
                <p class="description">
					<?php printf( __( 'You can get this token by registering <a href="%s" target="_blank">outgoing webhook</a>.', 'hameslack' ), 'https://api.slack.com/outgoing-webhooks' ) ?>
                </p>
            </td>
        </tr>
        <tr>
            <th>
                <label for="hameslack_regen"><?php _e( 'Hash Control', 'hameslack' ) ?></label>
            </th>
            <td>
                <label>
                    <input type="checkbox" name="hameslack_regen" id="hameslack_regen" value="1"/>
					<?php _e( 'Regenerate Hash', 'hameslack' ) ?>
                </label>
            </td>
        </tr>
    </table>
	<?php
} );

// Generate and save hash
add_action( 'save_post', function ( $post_id, $post ) {
	if ( 'slack-endpoint' !== $post->post_type ) {
		return;
	}
	if ( ! isset( $_POST['_hameslacknonce'] ) || ! wp_verify_nonce( $_POST['_hameslacknonce'], 'hameslack_hash' ) ) {
		return;
	}
	$current_hash = get_post_meta( $post->ID, '_hameslack_hash', true );
	if ( ( isset( $_POST['hameslack_regen'] ) && $_POST['hameslack_regen'] ) || ! $current_hash ) {
		$hash = wp_hash_password( current_time( 'mysql' ) . get_permalink( $post ) );
		foreach (
			[
				'$' => 'D',
				'/' => 'S',
			] as $str => $repl
		) {
			$hash = str_replace( $str, $repl, $hash );
		}
		update_post_meta( $post_id, '_hameslack_hash', $hash );
	}
	update_post_meta( $post_id, '_hameslack_token', $_POST['hameslack_token'] );
}, 10, 2 );

// Register rest route
add_action( 'rest_api_init', function () {
	register_rest_route( 'hameslack/v1', '/outgoing/(?P<hash>[^/]+)/?', [
		[
			'methods'  => 'POST',
			'args'     => [
				'hash'  => [
					'required' => true,
				],
				'token' => [
					'required' => true,
				],
			],
			'callback' => function ( $params ) {
				$posts = get_posts( [
					'post_type'        => 'slack-endpoint',
					'post_status'      => 'publish',
					'posts_per_page'   => 1,
					'suppress_filters' => false,
					'meta_query'       => [
						[
							'key'   => '_hameslack_hash',
							'value' => $params['hash'],
						],
						[
							'key'   => '_hameslack_token',
							'value' => $params['token'],
						],
					],
				] );
				if ( ! $posts ) {
					return new WP_REST_Response( [
						'text' => __( 'No API found. Token or URL is invalid.', 'hameslack' ),
					] );
				}
				/**
				 * hameslack_api_default_text
				 *
				 * Defautl text of response.
				 *
				 * @package hameslack
				 * @since 1.0.0
				 * @filter hameslack_rest_response
				 *
				 * @param string $text Default is '...'.
				 * @param WP_Post $post Endpoint post object.
				 *
				 * @return string
				 */
				$default_text = apply_filters( 'hameslack_api_default_text', '...', $posts[0] );

				/**
				 * hameslack_rest_response
				 *
				 * The response to slack outgoing webhook.
				 *
				 * @package hameslack
				 * @since 1.0.0
				 * @filter hameslack_rest_response
				 *
				 * @param array $response Response object. `text` is required.
				 * @param array $request posted request
				 * @param WP_Post $post Endpoint post object.
				 *
				 * @return array
				 */
				$response = apply_filters( 'hameslack_rest_response', [
					'text' => $default_text,
				], $_POST, $posts[0] );

				return new WP_REST_Response( $response );
			},
		],
	] );
} );

// Show columns on admin screen.
add_filter( 'manage_slack-endpoint_posts_columns', function ( $columns ) {
	$new_column = [];
	foreach ( $columns as $col => $label ) {
		if ( 'author' == $col ) {
			$new_column['endpoint'] = __( 'Endpoint', 'hameslack' );
		}
		$new_column[ $col ] = $label;
	}

	return $new_column;
} );

// Show column.
add_action( 'manage_slack-endpoint_posts_custom_column', function ( $col, $post_id ) {
	switch ( $col ) {
		case 'endpoint':
			$hash = get_post_meta( $post_id, '_hameslack_hash', true );
			if ( $hash ) {
				printf( '<code>%s</code>', esc_url( rest_url( "hameslack/v1/outgoing/{$hash}" ) ) );
			} else {
				printf( '<span style="color: #888;">%s</span>', esc_html__( 'Not Generated', 'hameslack' ) );
			}
			break;
		default:
			// Do nothing.
			break;
	}
}, 10, 2 );
