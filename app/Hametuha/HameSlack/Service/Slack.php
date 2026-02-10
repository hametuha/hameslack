<?php
namespace Hametuha\HameSlack\Service;


use Gianism\Service\AbstractService;

/**
 * Slack integrator for Gianism.
 */
class Slack extends AbstractService {

	/**
	 * URL prefix
	 *
	 * @var string
	 */
	public $url_prefix = 'slack-auth';

	/**
	 * Service name.
	 *
	 * @var string
	 */
	public $verbose_service_name = 'Slack';

	/**
	 * @var string
	 */
	public $umeta_id = '_wpg_slack_id';

	/**
	 * @var string
	 */
	public $umeta_token = '_wpg_slack_token';

	/**
	 * Team ID
	 *
	 * @var string
	 */
	public $slack_client_id = '';

	/**
	 * Team ID
	 *
	 * @var string
	 */
	public $slack_client_secret = '';

	/**
	 * Team ID
	 *
	 * @var string
	 */
	public $slack_team_id = '';

	/**
	 * Option key to copy
	 *
	 * @var array
	 */
	protected $option_keys = [
		'slack_enabled'       => false,
		'slack_client_id'     => '',
		'slack_client_secret' => '',
		'slack_team_id'       => '',
	];

	/**
	 * Constructor
	 *
	 * @param array $argument
	 */
	public function __construct( array $argument = [] ) {
		parent::__construct( $argument );
		// Filter rewrite name
		add_filter( 'gianism_filter_service_prefix', function ( $prefix ) {
			if ( 'slack-auth' === $prefix ) {
				$prefix = 'slack';
			}
			return $prefix;
		} );
		// Register Gianism CSS with proper dependency.
		add_action( 'init', function () {
			if ( wp_style_is( 'hameslack-gianism', 'registered' ) ) {
				// Already registered via wp-dependencies.json; add gianism dependency.
				global $wp_styles;
				$wp_styles->registered['hameslack-gianism']->deps[] = 'gianism';
			} else {
				// Fallback: manual registration.
				wp_register_style( 'hameslack-gianism', hameslack_asset_url() . '/css/hameslack-gianism.css', [ 'gianism' ], HAMESLACK_VERSION );
			}
		}, 20 );
		// Enqueue assets.
		add_action( 'login_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	/**
	 * Detect if user is connected to this service
	 *
	 * @param int $user_id
	 *
	 * @return bool
	 */
	public function is_connected( $user_id ) {
		return (bool) get_user_meta( $user_id, $this->umeta_id, true );
	}

	/**
	 * Disconnect user from this service
	 *
	 * @param int $user_id
	 *
	 * @return mixed
	 */
	public function disconnect( $user_id ) {
		delete_user_meta( $user_id, $this->umeta_id );
		delete_user_meta( $user_id, $this->umeta_token );
	}

	/**
	 * Get api URL
	 *
	 * @param string $action
	 *
	 * @return false|string
	 */
	protected function get_api_url( $action ) {
		switch ( $action ) {
			case 'connect':
			case 'login':
				$state = sha1( uniqid( 'slack_' . $action, true ) );
				$this->session->write( 'state', $state );
				/**
				 * hameslack_gianism_scope
				 *
				 * @param array  $scopes List of scopes.
				 * @param string $action 'connect' or 'login
				 */
				$scopes = apply_filters( 'hameslack_gianism_scope', [ 'identity.basic', 'identity.email' ], $action );
				return add_query_arg( [
					'client_id'    => $this->slack_client_id,
					'redirect_uri' => home_url( '/slack-auth/' ),
					'state'        => $state,
					'scope'        => implode( ',', $scopes ),
					'team'         => $this->slack_team_id,
				], 'https://slack.com/oauth/authorize' );
				break;
			default:
				return false;
				break;
		}
	}

	/**
	 * Handle default actions.
	 *
	 * @param string $action
	 */
	protected function handle_default( $action ) {
		// Get common values
		$redirect_url = $this->session->get( 'redirect_to' );
		$saved_state  = $this->session->get( 'state' );
		$state        = $this->input->get( 'state' );
		$code         = $this->input->get( 'code' );
		if ( 'access_denied' === $this->input->get( 'error' ) ) {
			$this->auth_fail( __( 'You denied authorization. Please try another method.', 'hameslack' ) );
			$redirect_url = wp_login_url( $redirect_url );
			$redirect_url = $this->filter_redirect( $redirect_url, 'login-failure' );
			wp_redirect( $redirect_url );
			exit;
		}
		switch ( $action ) {
			case 'login':
				try {
					$token     = $this->get_user_token( $code, $state, $saved_state );
					$user_info = $this->get_user_info( $token );
					$user_id   = $this->get_meta_owner( $this->umeta_id, $user_info->id );
					if ( ! $user_id ) {
						$this->test_user_can_register();
						// Check email
						if ( email_exists( $user_info->email ) ) {
							throw new \Exception( $this->duplicate_account_string() );
						}
						// Check user name
						$user_name = $this->valid_username_from_mail( $user_info->email );
						$user_id   = wp_create_user( $user_name, wp_generate_password(), $user_info->email );
						if ( is_wp_error( $user_id ) ) {
							throw new \Exception( $this->registration_error_string() );
						}
						// Update extra information
						update_user_meta( $user_id, $this->umeta_id, $user_info->id );
						update_user_meta( $user_id, $this->umeta_token, $token );
						update_user_meta( $user_id, 'nickname', $user_info->name );
						$this->db->update(
							$this->db->users,
							array(
								'display_name' => $user_info->name,
							),
							array( 'ID' => $user_id ),
							array( '%s' ),
							array( '%d' )
						);
						// Password is unknown
						$this->user_password_unknown( $user_id );
						$this->hook_connect( $user_id, $user_info, true );
						$this->welcome( $user_info->name );
					}
					wp_set_auth_cookie( $user_id, true );
					$redirect_url = $this->filter_redirect( $redirect_url, 'login' );
				} catch ( \Exception $e ) {
					$this->auth_fail( $e->getMessage() );
					$redirect_url = wp_login_url( $redirect_url );
					$redirect_url = $this->filter_redirect( $redirect_url, 'login-failure' );
				}
				wp_redirect( $redirect_url );
				exit;
				break;
			case 'connect':
				try {
					// Is user logged in?
					if ( ! is_user_logged_in() ) {
						throw new \Exception( $this->_( 'You must be logged in' ) );
					}
					// Get user info
					$token     = $this->get_user_token( $code, $state, $saved_state );
					$user_info = $this->get_user_info( $token );
					$owner     = $this->get_meta_owner( $this->umeta_id, $user_info->id );
					if ( $owner ) {
						throw new \Exception( $this->duplicate_account_string() );
					}
					// O.k.
					update_user_meta( get_current_user_id(), $this->umeta_id, $user_info->id );
					update_user_meta( get_current_user_id(), $this->umeta_token, $token );
					$this->hook_connect( get_current_user_id(), $user_info, false );
					$this->welcome( (string) $user_info->name );
					$redirect_url = $this->filter_redirect( $redirect_url, 'connect' );
				} catch ( \Exception $e ) {
					$this->auth_fail( $e->getMessage() );
					$redirect_url = $this->filter_redirect( $redirect_url, 'connect-failure' );
				}
				wp_redirect( $redirect_url );
				exit;
				break;
			default:
				/**
				 * @see \Gianism\Service\Facebook
				 */
				do_action( 'gianism_extra_action', $this->service_name, $action, [
					'redirect_to' => $redirect_url,
				] );
				// translators: %1$s is URL, %2$s is service name.
				$this->input->wp_die( sprintf( __( 'Sorry, but wrong access. Please go back to <a href="%1$s">%2$s</a>.', 'wp-gianism' ), home_url( '/' ), get_bloginfo( 'name' ) ), 500, false );
				break;
		}
	}

	/**
	 * Get user information.
	 *
	 * @param $code
	 * @param $state
	 * @param $saved_state
	 *
	 * @throws \Exception
	 * @return string
	 */
	public function get_user_token( $code, $state, $saved_state ) {
		if ( $state !== $saved_state ) {
			throw new \Exception( __( 'Code signing is wrong and your access is invalid. Please try again later.', 'hameslack' ), 401 );
		}
		$url      = add_query_arg( [
			'client_id'     => $this->slack_client_id,
			'client_secret' => $this->slack_client_secret,
			'code'          => $code,
			'redirect_uri'  => home_url( '/slack-auth/' ),
		], 'https://slack.com/api/oauth.access' );
		$response = wp_remote_get( $url );
		if ( is_wp_error( $response ) ) {
			throw new \Exception( $response->get_error_message(), 500 );
		}
		$json = json_decode( $response['body'] );
		return $json->access_token;
	}

	/**
	 * Get user information.
	 *
	 * @param $token
	 *
	 * @return \stdClass
	 * @throws \Exception
	 */
	public function get_user_info( $token ) {
		$result = wp_remote_get( add_query_arg( [
			'token' => $token,
		], 'https://slack.com/api/users.identity' ) );
		if ( is_wp_error( $result ) ) {
			throw new \Exception( $result->get_error_message(), 500 );
		}
		$json = json_decode( $result['body'] );
		if ( ! $json || ! $json->ok ) {
			throw new \Exception( __( 'Failed to get your information.', 'hameslack' ), 500 );
		}
		return $json->user;
	}

	/**
	 * Enqueue assets.
	 */
	public function enqueue_assets() {
		wp_enqueue_style( 'hameslack-gianism' );
	}

	/**
	 * Get template screen
	 *
	 * @param string $template_dir
	 * @return string
	 */
	public function get_admin_template( $template_dir ) {
		switch ( $template_dir ) {
			case 'setting':
			case 'setup':
				return HAMESLACK_ROOT_DIR . '/templates/' . $template_dir . '.php';
				break;
			default:
				return parent::get_admin_template( $template_dir );
				break;
		}
	}
}
