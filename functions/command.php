<?php
/**
 * Command class
 */
if ( ! defined( 'WP_CLI' ) ) {
	return;
}

/**
 * Command Class for HameSlack.
 *
 * @package hameslack
 * @since 1.0.0
 */
class HameSlackCommand extends WP_CLI_Command {

	/**
	 * Show channel list
	 *
	 */
	public function channels() {
		$result = hameslack_bot_request( 'GET', 'channels.list' );
		if ( is_wp_error( $result ) ) {
			WP_CLI::error( $result->get_error_message() );
		}
		$table = new cli\Table();
		$table->setHeaders( [ 'Name', 'id', 'Created', 'Member' ] );
		foreach ( $result->channels as $channel ) {
			$table->addRow( [
				$channel->name,
				$channel->id,
				date_i18n( 'Y-m-d H:i', $channel->created ),
				$channel->num_members,
			] );
		}
		$table->display();
	}

	/**
	 * Show channel list
	 *
	 * ## OPTIONS
	 * : <channel>
	 *   Channel name.
	 *
	 * @synopsis <channel> [--latest=<latest>] [--oldest=<oldest>] [--count=<count>] [--inclusive] [--unreads]
	 * @param array $args
	 * @param array $assoc
	 */
	public function history( $args, $assoc ) {
		list( $channel ) = $args;
		$args            = wp_parse_args( $assoc, [
			'count'     => 10,
			'inclusive' => 0,
			'unreads'   => 0,
		] );
		// phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
		$latest   = isset( $assoc['latest'] ) ? $assoc['latest'] : current_time( 'timestamp' );
		$oldest   = isset( $assoc['oldest'] ) ? $assoc['oldest'] : 0;
		$messages = hameslack_channel_history( $channel, $oldest, $latest, $args );
		if ( is_wp_error( $messages ) ) {
			WP_CLI::error( $messages->get_error_message() );
		}
		$table = new cli\Table();
		$table->setHeaders( [ 'User', 'Time', 'Text' ] );
		foreach ( $messages as $message ) {
			$table->addRow( [
				$message->user,
				date_i18n( 'Y-m-d H:i', (int) $message->ts ),
				mb_substr( $message->text, 0, 20, 'utf-8' ) . '...',
			] );
		}
		$table->display();
	}

	/**
	 * Get user list of Slack
	 */
	public function members() {
		$users = hameslack_members();
		if ( is_wp_error( $users ) ) {
			WP_CLI::error( $users->get_error_message() );
		}
		$table = new cli\Table();
		$table->setHeaders( [ 'Name', 'ID', 'Full Name', 'Mail', 'Admin' ] );
		foreach ( $users as $user ) {
			$table->addRow( [
				$user->name,
				$user->id,
				$user->real_name,
				$user->profile->email,
				$user->is_admin ? '☑︎' : '-',
			] );
		}
		$table->display();
	}

	/**
	 * Get channel ID by name
	 *
	 * ## OPTIONS
	 * : <channel>
	 *   Channel label. e.g. general
	 *
	 * @synopsis <channel>
	 * @param array $args
	 */
	public function channel_id( $args ) {
		$channel = hameslack_channel_id( $args[0] );
		if ( is_wp_error( $channel ) ) {
			WP_CLI::error( $channel->get_error_message() );
		}
		WP_CLI::success( sprintf( 'ID = %s', $channel ) );
	}

	/**
	 * Invite user to slack.
	 *
	 * @synopsis <user>
	 * @param array $args
	 * @return void
	 */
	public function invite( $args ) {
		list( $user_id ) = $args;
		$user            = get_userdata( $user_id );
		if ( ! $user ) {
			// translators: %d is user ID.
			WP_CLI::error( sprintf( __( 'User #%d not found.', 'hameslack' ), $user_id ) );
		}
		$table = new cli\Table();
		$table->setHeaders( [ 'Property', 'Value' ] );
		foreach ( [
			[ 'ID', $user->ID ],
			[ 'Name', $user->display_name ],
			[ 'Mail', $user->user_email ],
			[ 'Role', implode( ',', $user->roles ) ],
			[ 'Registered', $user->user_registered ],
		] as $row ) {
			$table->addRow( $row );
		}
		$table->display();
		WP_CLI::confirm( __( 'Are you sure to invite this user?', 'hametslack' ) );
		$result = hameslack_user_invite( $user_id );
		if ( is_wp_error( $result ) ) {
			WP_CLI::error( $result->get_error_message() );
		}
		WP_CLI::success( __( 'Successfully send invitation to slack.', 'hameslack' ) );
	}
}
